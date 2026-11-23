@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card p-4">
            <h4 class="fw-bold mb-2">Verify Clearance Certificate</h4>
            <p class="text-muted small mb-3">Scan the QR code on the certificate or enter the Serial Number and Verification Code below.</p>

            <form method="GET" action="{{ route('verify.show') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="serial" value="{{ $serial }}" class="form-control" placeholder="Serial (e.g. AUC-CLR-2026-000001)" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="code" value="{{ $code }}" class="form-control" placeholder="Verification Code (optional)">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Verify</button>
                </div>
            </form>

            @if($serial !== '')
                @if($record && $record->certificate_status === 'valid')
                    <div class="alert alert-success mb-0">
                        <h6 class="fw-bold mb-1">VALID CERTIFICATE</h6>
                        <div><strong>Serial:</strong> {{ $record->certificate_serial }} &bull; <strong>Code:</strong> {{ $record->verification_code }}</div>
                        <div><strong>Student:</strong> {{ $record->user->name }} ({{ $record->user->student_id }})</div>
                        <div><strong>Department:</strong> {{ $record->user->department }} &bull; <strong>Batch:</strong> {{ $record->user->batch }}</div>
                        <div><strong>Reason:</strong> {{ $record->reason }} &bull; <strong>Issued:</strong> {{ $record->issued_at?->format('d F Y') }}</div>
                    </div>
                @elseif($record && $record->certificate_status === 'revoked')
                    <div class="alert alert-danger mb-0">
                        <h6 class="fw-bold mb-1">REVOKED CERTIFICATE</h6>
                        <div>Certificate <strong>{{ $record->certificate_serial }}</strong> was revoked by Admas University Registrar.</div>
                    </div>
                @else
                    <div class="alert alert-danger mb-0">
                        <h6 class="fw-bold mb-1">INVALID CERTIFICATE</h6>
                        <div>No certificate matched serial <strong>{{ $serial }}</strong>{{ $code ? " with code {$code}" : '' }}.</div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection