<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClearanceNotification;
use App\Models\ClearanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficerClearanceController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $sequence = ClearanceRequest::officeSequence();

        abort_unless(isset($sequence[$user->role]) && $user->role !== 'registrar', 403);

        $officeLabel = $sequence[$user->role]['label'];
        $statusCol = $sequence[$user->role]['status'];
        $search = trim((string) $request->query('search', ''));

        $allPending = ClearanceRequest::with('user')
            ->where($statusCol, 'pending')
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        // Only show requests whose previous sequential offices are already approved
        $pendingRequests = $allPending->filter(fn (ClearanceRequest $req) => $req->isReadyForOffice($user->role));

        $recentDecisions = ClearanceRequest::with('user')
            ->whereIn($statusCol, ['approved', 'rejected'])
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('officer.dashboard', compact('officeLabel', 'statusCol', 'pendingRequests', 'recentDecisions', 'search'));
    }

    public function decide(Request $request, ClearanceRequest $clearance)
    {
        $user = Auth::user();
        $sequence = ClearanceRequest::officeSequence();

        abort_unless(isset($sequence[$user->role]) && $user->role !== 'registrar', 403);
        abort_unless($clearance->isReadyForOffice($user->role), 403, 'Previous office approval is required first.');

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'remark' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->decision === 'rejected' && trim((string) $request->remark) === '') {
            return back()->withErrors(['remark' => 'Remark is required when rejecting a request.']);
        }

        $officeLabel = $sequence[$user->role]['label'];
        $statusCol = $sequence[$user->role]['status'];
        $remarkCol = $sequence[$user->role]['remark'];

        $clearance->{$statusCol} = $request->decision;
        $clearance->{$remarkCol} = $request->remark ?: null;
        $clearance->recalculateOverallStatus();

        $statusWord = ucfirst($request->decision);
        $remarkText = $request->remark ? " ({$request->remark})" : '';

        ClearanceNotification::create([
            'user_id' => $clearance->user_id,
            'message' => "{$officeLabel}: {$statusWord}{$remarkText} on Request #{$clearance->id}.",
        ]);

        AuditLog::record("Office {$statusWord}", "{$officeLabel} {$request->decision} Request #{$clearance->id}.");

        return back()->with('success', "Request #{$clearance->id} marked as {$request->decision}.");
    }
}
