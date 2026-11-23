<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClearanceNotification;
use App\Models\ClearanceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrarController extends Controller
{
    public function dashboard()
    {
        $pendingCount = ClearanceRequest::where('overall_status', 'pending')->count();
        $rejectedCount = ClearanceRequest::where('overall_status', 'rejected')->count();

        // Requests approved by Department, Library, Store, and Finance — ready for Registrar approval & certificate issuance
        $readyToIssue = ClearanceRequest::with('user')
            ->where('department_status', 'approved')
            ->where('library_status', 'approved')
            ->where('store_status', 'approved')
            ->where('finance_status', 'approved')
            ->whereNull('certificate_serial')
            ->latest()
            ->get();

        $issuedCertificates = ClearanceRequest::with('user')
            ->whereNotNull('certificate_serial')
            ->latest('issued_at')
            ->get();

        return view('registrar.dashboard', compact('pendingCount', 'rejectedCount', 'readyToIssue', 'issuedCertificates'));
    }

    public function issueCertificate(ClearanceRequest $clearance)
    {
        abort_unless(
            $clearance->department_status === 'approved' &&
            $clearance->library_status === 'approved' &&
            $clearance->store_status === 'approved' &&
            $clearance->finance_status === 'approved',
            400
        );

        if (!$clearance->certificate_serial) {
            $year = now()->format('Y');
            $nextNumber = str_pad((string) $clearance->id, 6, '0', STR_PAD_LEFT);
            $clearance->registrar_status = 'approved';
            $clearance->certificate_serial = "AUC-CLR-{$year}-{$nextNumber}";
            $clearance->verification_code = strtoupper(Str::random(8));
            $clearance->certificate_status = 'valid';
            $clearance->issued_at = now();
            $clearance->recalculateOverallStatus();

            ClearanceNotification::create([
                'user_id' => $clearance->user_id,
                'message' => "Registrar approved and issued certificate {$clearance->certificate_serial} (Code: {$clearance->verification_code}).",
            ]);

            AuditLog::record('Certificate Issued', "Issued {$clearance->certificate_serial} for Request #{$clearance->id}.");
        }

        return back()->with('success', "Certificate {$clearance->certificate_serial} issued successfully.");
    }

    public function rejectRequest(Request $request, ClearanceRequest $clearance)
    {
        $request->validate(['remark' => ['required', 'string', 'max:255']]);

        $clearance->registrar_status = 'rejected';
        $clearance->registrar_remark = $request->remark;
        $clearance->recalculateOverallStatus();

        ClearanceNotification::create([
            'user_id' => $clearance->user_id,
            'message' => "Registrar: Rejected ({$request->remark}) on Request #{$clearance->id}.",
        ]);

        AuditLog::record('Registrar Rejected', "Registrar rejected Request #{$clearance->id}: {$request->remark}");

        return back()->with('success', "Request #{$clearance->id} rejected by Registrar.");
    }

    public function revokeCertificate(ClearanceRequest $clearance)
    {
        abort_unless((bool) $clearance->certificate_serial, 404);

        $clearance->certificate_status = 'revoked';
        $clearance->save();

        ClearanceNotification::create([
            'user_id' => $clearance->user_id,
            'message' => "Certificate {$clearance->certificate_serial} has been revoked by the Registrar.",
        ]);

        AuditLog::record('Certificate Revoked', "Revoked {$clearance->certificate_serial}.");

        return back()->with('success', "Certificate {$clearance->certificate_serial} has been revoked.");
    }

    public function downloadPdf(ClearanceRequest $clearance)
    {
        abort_unless((bool) $clearance->certificate_serial, 404);

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
