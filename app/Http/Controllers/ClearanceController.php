<?php

namespace App\Http\Controllers;

use App\Models\ClearanceNotification;
use App\Models\ClearanceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ClearanceController extends Controller
{
    private const OFFICE_MAP = [
        'department_head' => ['label' => 'Department Head', 'status' => 'department_status', 'remark' => 'department_remark'],
        'library'         => ['label' => 'Library', 'status' => 'library_status', 'remark' => 'library_remark'],
        'finance'         => ['label' => 'Finance', 'status' => 'finance_status', 'remark' => 'finance_remark'],
        'student_affairs' => ['label' => 'Student Affairs', 'status' => 'student_affairs_status', 'remark' => 'student_affairs_remark'],
    ];

    public function studentDashboard(): View
    {
        $user = Auth::user();
        $requests = ClearanceRequest::where('user_id', $user->id)->latest()->get();
        $notifications = $user->notifications()->take(10)->get();

        return view('student.dashboard', compact('requests', 'notifications'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        ClearanceRequest::create([
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'department_status' => 'pending',
            'library_status' => 'pending',
            'finance_status' => 'pending',
            'student_affairs_status' => 'pending',
            'registrar_status' => 'pending',
            'overall_status' => 'pending',
        ]);

        return redirect()->route('student.dashboard')->with('success', 'Request submitted.');
    }

    public function markNotificationsRead(): RedirectResponse
    {
        ClearanceNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }

    public function officerDashboard(Request $request): View
    {
        $user = Auth::user();
        $office = self::OFFICE_MAP[$user->role];
        $statusCol = $office['status'];
        $search = trim((string) $request->query('search', ''));

        $pendingQuery = ClearanceRequest::with('user')->where($statusCol, 'pending');

        if ($search !== '') {
            $pendingQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $pendingRequests = $pendingQuery->latest()->get();

        $recentDecisions = ClearanceRequest::with('user')
            ->whereIn($statusCol, ['approved', 'rejected'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('officer.dashboard', [
            'officeLabel' => $office['label'],
            'statusCol' => $statusCol,
            'pendingRequests' => $pendingRequests,
            'recentDecisions' => $recentDecisions,
            'search' => $search,
        ]);
    }

    public function officerDecision(Request $request, ClearanceRequest $clearanceRequest): RedirectResponse
    {
        $office = self::OFFICE_MAP[Auth::user()->role];
        $decision = $request->input('decision');

        $rules = [
            'decision' => ['required', 'in:approved,rejected'],
            'remark' => $decision === 'rejected' ? ['required', 'string', 'max:500'] : ['nullable', 'string', 'max:500'],
        ];

        $validated = $request->validate($rules, [
            'remark.required' => 'Remark is required when rejecting a request.',
        ]);

        $clearanceRequest->{$office['status']} = $validated['decision'];
        $clearanceRequest->{$office['remark']} = $validated['remark'] ?? null;

        $allFour = [
            $clearanceRequest->department_status,
            $clearanceRequest->library_status,
            $clearanceRequest->finance_status,
            $clearanceRequest->student_affairs_status,
        ];

        if (in_array('rejected', $allFour, true)) {
            $clearanceRequest->overall_status = 'rejected';
        } elseif (count(array_unique($allFour)) === 1 && $allFour[0] === 'approved') {
            $clearanceRequest->overall_status = 'approved';
        } else {
            $clearanceRequest->overall_status = 'pending';
        }

        $clearanceRequest->save();

        $actionWord = $validated['decision'] === 'approved' ? 'approved' : 'rejected';
        ClearanceNotification::create([
            'user_id' => $clearanceRequest->user_id,
            'message' => "{$office['label']} {$actionWord} your clearance.",
        ]);

        return back()->with('success', "Clearance {$actionWord}.");
    }

    public function registrarDashboard(): View
    {
        $pendingCount = ClearanceRequest::where('overall_status', 'pending')->count();
        $rejectedCount = ClearanceRequest::where('overall_status', 'rejected')->count();

        $readyToIssue = ClearanceRequest::with('user')
            ->where('overall_status', 'approved')
            ->whereNull('certificate_serial')
            ->latest()
            ->get();

        $issuedCertificates = ClearanceRequest::with('user')
            ->whereNotNull('certificate_serial')
            ->latest('issued_at')
            ->get();

        return view('registrar.dashboard', compact(
            'pendingCount',
            'rejectedCount',
            'readyToIssue',
            'issuedCertificates'
        ));
    }

    public function issueCertificate(ClearanceRequest $clearanceRequest): RedirectResponse
    {
        abort_if($clearanceRequest->overall_status !== 'approved', 403);

        $year = now()->year;
        $serial = sprintf('AUC-CLR-%d-%06d', $year, $clearanceRequest->id);

        $clearanceRequest->update([
            'registrar_status' => 'approved',
            'certificate_serial' => $serial,
            'certificate_status' => 'valid',
            'issued_at' => now(),
        ]);

        ClearanceNotification::create([
            'user_id' => $clearanceRequest->user_id,
            'message' => "Registrar issued your clearance certificate ({$serial}).",
        ]);

        return back()->with('success', "Certificate {$serial} issued.");
    }

    public function revokeCertificate(ClearanceRequest $clearanceRequest): RedirectResponse
    {
        $clearanceRequest->update([
            'certificate_status' => 'revoked',
        ]);

        ClearanceNotification::create([
            'user_id' => $clearanceRequest->user_id,
            'message' => "Your certificate ({$clearanceRequest->certificate_serial}) was revoked by the Registrar.",
        ]);

        return back()->with('success', "Certificate {$clearanceRequest->certificate_serial} revoked.");
    }

    public function verifyForm(Request $request): View
    {
        $serial = trim((string) $request->query('serial', ''));
        $certificate = null;
        $searched = $serial !== '';

        if ($searched) {
            $certificate = ClearanceRequest::with('user')
                ->where('certificate_serial', $serial)
                ->first();
        }

        return view('verify', compact('serial', 'certificate', 'searched'));
    }

    public function downloadCertificate(ClearanceRequest $clearanceRequest)
    {
        $user = Auth::user();
        if ($user->role === 'student') {
            abort_if($clearanceRequest->user_id !== $user->id, 403);
        }

        abort_if(!$clearanceRequest->certificate_serial || $clearanceRequest->certificate_status !== 'valid', 403, 'Certificate is not issued or has been revoked.');

        $verifyUrl = route('verify.form', ['serial' => $clearanceRequest->certificate_serial]);
        $qrCode = base64_encode(QrCode::format('svg')->size(110)->generate($verifyUrl));

        $pdf = Pdf::loadView('student.certificate', [
            'clearance' => $clearanceRequest->load('user'),
            'qrCode' => $qrCode,
        ])->setPaper('a4', 'landscape');

        $safeStudentId = str_replace(['/', '\\'], '_', $clearanceRequest->user->student_id ?? 'student');

        return $pdf->download("Admas_Clearance_{$safeStudentId}.pdf");
    }
}