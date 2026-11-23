@extends('layouts.app')
@section('title', 'Login - Admas Clearance')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <h4 class="fw-normal mb-3">Login</h4>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-medium">E-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="loginPassword" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()">Show</button>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-admas w-100 mb-3">Login</button>

                    <div class="small">
                        New student? <a href="{{ route('register') }}">Create an account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    const input = document.getElementById('loginPassword');
    const btn = event.target;
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}
</script>
@endsection
