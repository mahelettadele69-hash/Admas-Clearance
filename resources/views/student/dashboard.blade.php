@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="fw-bold mb-1">Student Clearance</h4>
                    <div class="text-muted small">
                        {{ $user->name }} &bull; ID {{ $user->student_id }} &bull; {{ $user->department }} &bull; Batch {{ $user->batch }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('student.clearance.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label class="form-label fw-semibold small">Reason for clearance</label>
                    <select name="reason" class="form-select">
                        <option value="Graduation">Graduation</option>
                        <option value="Semester Break">Semester Break</option>
                        <option value="Withdrawal">Withdrawal</option>
                        <option value="Transfer">Transfer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Submit clearance request</button>
                </div>
            </form>
        </div>

        @forelse($requests as $req)
            @php $pct = $req->progressPercentage(); @endphp
            <div class="card p-4 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="fw-bold">Request #{{ $req->id }}</span>
                        <span class="badge bg-secondary ms-2">{{ $req->reason }}</span>
                    </div>
                    <span class="badge bg-{{ $req->overall_status === 'approved' ? 'success' : ($req->overall_status === 'rejected' ? 'danger' : 'warning text-dark') }}">
                        {{ strtoupper($req->overall_status) }} ({{ $pct }}%)
                    </span>
                </div>

                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-2">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Office</th>
                                <th>Status</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\ClearanceRequest::officeSequence() as $key => $meta)
                                @php
                                    $st = $req->{$meta['status']};
                                    $rm = $req->{$meta['remark']};
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $meta['label'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($st) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $rm ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($req->overall_status === 'rejected')
                    <form method="POST" action="{{ route('student.clearance.resubmit', $req) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm fw-semibold">
                            Resubmit Clearance Request
                        </button>
                    </form>
                @endif

                @if($req->certificate_serial && $req->certificate_status === 'valid')
                    <div class="alert alert-success d-flex justify-content-between align-items-center mt-3 mb-0">
                        <div>
                            <strong>Certificate Issued:</strong> {{ $req->certificate_serial }}
                            <span class="ms-2 badge bg-dark">Verification Code: {{ $req->verification_code }}</span>
                        </div>
                        <a href="{{ route('student.clearance.certificate', $req) }}" class="btn btn-success btn-sm">Download PDF</a>
                    </div>
                @elseif($req->certificate_status === 'revoked')
                    <div class="alert alert-danger mt-3 mb-0">
                        Certificate <strong>{{ $req->certificate_serial }}</strong> has been revoked by the Registrar.
                    </div>
                @endif
            </div>
        @empty
            <div class="card p-4 text-center text-muted">No clearance requests submitted yet.</div>
        @endforelse
    </div>

    <div class="col-lg-4">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Notifications</h6>
                <form method="POST" action="{{ route('student.notifications.read') }}">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                </form>
            </div>
            @forelse($notifications as $note)
                <div class="border-bottom py-2 small {{ $note->is_read ? 'text-muted' : 'fw-semibold' }}">
                    {{ $note->message }}
                    <div class="text-muted" style="font-size: 11px;">{{ $note->created_at->diffForHumans() }}</div>
                </div>
            @empty
                <div class="text-muted small">No notifications yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection