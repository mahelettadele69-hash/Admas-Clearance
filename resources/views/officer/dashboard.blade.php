@extends('layouts.app')
@section('title', $officeLabel . ' - Admas Clearance')

@section('content')
<h4 class="fw-normal mb-3">{{ $officeLabel }} - pending requests</h4>

@if (session('success'))
    <div class="alert alert-success border-0 shadow-sm py-2 small">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm py-2 small">{{ $errors->first() }}</div>
@endif

<form method="GET" action="{{ route('officer.dashboard') }}" class="d-flex gap-2 mb-3" style="max-width: 420px;">
    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by student name or ID">
    <button type="submit" class="btn btn-admas px-4">Search</button>
</form>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Student</th>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Reason</th>
                    <th style="min-width: 360px;">Decision</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingRequests as $req)
                    <tr>
                        <td class="small">{{ $req->user->name }}</td>
                        <td class="small">{{ $req->user->student_id }}</td>
                        <td class="small">{{ $req->user->department }}</td>
                        <td class="small">{{ $req->reason }}</td>
                        <td>
                            <form method="POST" action="{{ route('officer.decide', $req) }}" class="d-flex gap-2">
                                @csrf
                                <input type="text" name="remark" class="form-control form-control-sm" placeholder="Remark (required to reject)">
                                <button type="submit" name="decision" value="approved" class="btn btn-sm btn-success">Approve</button>
                                <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted small py-4">No pending requests for {{ $officeLabel }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<h6 class="fw-semibold small mb-2">Recent decisions</h6>
<ul class="small text-muted ps-3">
    @forelse ($recentDecisions as $dec)
        <li>{{ $dec->user->name }} - {{ $dec->{$statusCol} }} ({{ $dec->updated_at->format('d M Y H:i') }})</li>
    @empty
        <li>No recent decisions yet.</li>
    @endforelse
</ul>
@endsection