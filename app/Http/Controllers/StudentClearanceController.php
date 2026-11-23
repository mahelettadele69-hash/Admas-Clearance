<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClearanceNotification;
use App\Models\ClearanceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentClearanceController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $requests = ClearanceRequest::where('user_id', $user->id)->latest()->get();
        $notifications = ClearanceNotification::where('user_id', $user->id)->latest()->take(15)->get();

        return view('student.dashboard', compact('user', 'requests', 'notifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:100'],
        ]);

        $user = Auth::user();

        $hasActive = ClearanceRequest::where('user_id', $user->id)
            ->where('overall_status', 'pending')
            ->exists();

        if ($hasActive) {
            return back()->withErrors(['reason' => 'You already have a pending clearance request.']);
        }

        $clearance = ClearanceRequest::create([
            'user_id' => $user->id,
            'reason' => $request->reason,
        ]);

        ClearanceNotification::create([
            'user_id' => $user->id,
            'message' => "Clearance request #{$clearance->id} ({$clearance->reason}) submitted to Academic Department.",
        ]);

        AuditLog::record('Clearance Submitted', "Request #{$clearance->id} submitted by {$user->student_id}.");

        return back()->with('success', 'Clearance request submitted successfully.');
    }

    public function resubmit(ClearanceRequest $clearance)
    {
        abort_unless($clearance->user_id === Auth::id(), 403);
        abort_unless($clearance->overall_status === 'rejected', 400);

        // Reset any rejected office back to pending so the workflow can continue
        foreach (['department_status', 'library_status', 'store_status', 'finance_status', 'registrar_status'] as $col) {
            if ($clearance->{$col} === 'rejected') {
                $clearance->{$col} = 'pending';
            }
        }

        $clearance->overall_status = 'pending';
        $clearance->save();

        ClearanceNotification::create([
            'user_id' => Auth::id(),
            'message' => "Clearance request #{$clearance->id} was resubmitted for review.",
        ]);

        AuditLog::record('Clearance Resubmitted', "Request #{$clearance->id} resubmitted by student.");

        return back()->with('success', 'Your clearance request has been resubmitted.');
    }

    public function markNotificationsRead()
    {
        ClearanceNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back();
    }

    public function downloadCertificate(ClearanceRequest $clearance)
    {
        abort_unless($clearance->user_id === Auth::id(), 403);
        abort_unless($clearance->certificate_serial && $clearance->certificate_status === 'valid', 404);

        $verifyUrl = route('verify.show', [
            'serial' => $clearance->certificate_serial,
            'code' => $clearance->verification_code,
        ]);
        $qrCode = base64_encode(QrCode::format('svg')->size(110)->generate($verifyUrl));

        $pdf = Pdf::loadView('student.certificate', compact('clearance', 'qrCode'))
            ->setPaper('a4', 'landscape');

        $safeId = str_replace('/', '_', $clearance->user->student_id ?? $clearance->id);
        return $pdf->download("Admas_Clearance_{$safeId}.pdf");
    }
}