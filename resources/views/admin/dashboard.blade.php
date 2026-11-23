@extends('layouts.app')

@section('content')
<h4 class="fw-bold mb-3">System Administrator – Users, Rules & Audit Logs</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Students</div><div class="fs-4 fw-bold">{{ $stats['students'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Office Staff</div><div class="fs-4 fw-bold">{{ $stats['staff'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Total Requests</div><div class="fs-4 fw-bold">{{ $stats['total_requests'] }}</div></div></div>
    <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Issued Certificates</div><div class="fs-4 fw-bold text-success">{{ $stats['issued_certs'] }}</div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3">Create Office / Staff Account</h6>
            <form method="POST" action="{{ route('admin.staff.create') }}">
                @csrf
                <div class="mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Full Name" required></div>
                <div class="mb-2"><input type="email" name="email" class="form-control form-control-sm" placeholder="Email (@admas.edu.et)" required></div>
                <div class="mb-2">
                    <select name="role" class="form-select form-select-sm">
                        <option value="department_head">Academic Department</option>
                        <option value="library">Library</option>
                        <option value="store">Store</option>
                        <option value="finance">Finance</option>
                        <option value="registrar">Registrar</option>
                        <option value="admin">System Administrator</option>
                    </select>
                </div>
                <div class="mb-2"><input type="text" name="department" class="form-control form-control-sm" placeholder="Department / Office Name" required></div>
                <div class="mb-3"><input type="password" name="password" class="form-control form-control-sm" placeholder="Password (min 6 chars)" required></div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Create Account</button>
            </form>
        </div>

        <div class="card p-4">
            <h6 class="fw-bold mb-2">Configured Sequential Clearance Order</h6>
            <ol class="mb-0 small">
                <li><strong>Academic Department</strong> (<code>dept@admas.edu.et</code>)</li>
                <li><strong>Library</strong> (<code>library@admas.edu.et</code>)</li>
                <li><strong>Store</strong> (<code>store@admas.edu.et</code>)</li>
                <li><strong>Finance</strong> (<code>finance@admas.edu.et</code>)</li>
                <li><strong>Registrar</strong> (<code>registrar@admas.edu.et</code>) — Final Approval & PDF Certificate</li>
            </ol>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">System Audit Logs (Latest 25 Actions)</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped small">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $log->user?->email ?? 'System' }}</td>
                                <td class="fw-semibold">{{ $log->action }}</td>
                                <td>{{ $log->details }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No audit logs recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection