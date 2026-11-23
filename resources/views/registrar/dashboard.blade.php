@extends('layouts.app')

@section('content')
<h4 class="fw-bold mb-3">Registrar – Certificates & Final Approval</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Pending</div>
            <div class="fs-4 fw-bold">{{ $pendingCount }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Rejected</div>
            <div class="fs-4 fw-bold text-danger">{{ $rejectedCount }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Ready to issue</div>
            <div class="fs-4 fw-bold text-primary">{{ $readyToIssue->count() }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Issued</div>
            <div class="fs-4 fw-bold text-success">{{ $issuedCertificates->count() }}</div>
        </div>
    </div>
</div>

<div class="card p-4 mb-4">
    <h6 class="fw-bold mb-3">Cleared by Department, Library, Store & Finance – Ready for Registrar</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Department</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($readyToIssue as $req)
                    <tr>
                        <td>#{{ $req->id }}</td>
                        <td>{{ $req->user->name }}</td>
                        <td>{{ $req->user->student_id }}</td>
                        <td>{{ $req->user->department }}</td>
                        <td>{{ $req->reason }}</td>
                        <td class="d-flex gap-2">
                            <form method="POST" action="{{ route('registrar.issue', $req) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Approve & Issue Certificate</button>
                            </form>
                            <form method="POST" action="{{ route('registrar.reject', $req) }}" class="d-flex gap-1">
                                @csrf
                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="Reject remark" required>
                                <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No requests waiting for Registrar approval.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4">
    <h6 class="fw-bold mb-3">Issued Certificates</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Serial</th>
                    <th>Verification Code</th>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Issued</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issuedCertificates as $cert)
                    <tr>
                        <td class="fw-semibold">{{ $cert->certificate_serial }}</td>
                        <td><code>{{ $cert->verification_code }}</code></td>
                        <td>{{ $cert->user->name }}</td>
                        <td>{{ $cert->user->student_id }}</td>
                        <td>{{ $cert->issued_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $cert->certificate_status === 'valid' ? 'success' : 'danger' }}">
                                {{ strtoupper($cert->certificate_status) }}
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('registrar.certificate.pdf', $cert) }}" class="btn btn-outline-primary btn-sm">PDF</a>
                            @if($cert->certificate_status === 'valid')
                                <form method="POST" action="{{ route('registrar.revoke', $cert) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Revoke</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No certificates issued yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection