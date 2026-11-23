@extends('layouts.app')
@section('title', 'Student Registration - Admas Clearance')

@section('content')
<div class="row justify-content-center mt-3">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h4 class="fw-normal mb-3">Student registration</h4>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.post') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Full name</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Student ID</label>
                            <input type="text" name="student_id" value="{{ old('student_id') }}" class="form-control" placeholder="0062/24" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Department</label>
                            <input type="text" name="department" value="{{ old('department', 'Computer Science') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Batch / year</label>
                            <input type="text" name="batch" value="{{ old('batch', '2024') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Password (min 8)</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Confirm password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-4 d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-admas px-4">Register</button>
                        <a href="{{ route('login') }}" class="small">Back to login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection