<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admas University Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar-admas { background-color: #152b48; }
        .card { border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); border: 1px solid #e3e6ea; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-admas py-2">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">Admas University Clearance</a>
        <div class="navbar-nav me-auto">
            @auth
                @if(Auth::user()->role === 'student')
                    <a class="nav-link text-white-50" href="{{ route('student.dashboard') }}">Dashboard</a>
                @elseif(Auth::user()->role === 'registrar')
                    <a class="nav-link text-white-50" href="{{ route('registrar.dashboard') }}">Certificates</a>
                @elseif(Auth::user()->role === 'admin')
                    <a class="nav-link text-white-50" href="{{ route('admin.dashboard') }}">Admin Panel</a>
                @else
                    <a class="nav-link text-white-50" href="{{ route('officer.dashboard') }}">Pending requests</a>
                @endif
            @endauth
            <a class="nav-link text-white-50" href="{{ route('verify.show') }}">Verify certificate</a>
        </div>

        @auth
            <div class="d-flex align-items-center gap-3">
                <span class="text-white small">
                    {{ Auth::user()->name }} ({{ Auth::user()->role }})
                </span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        @else
            <div class="d-flex gap-2">
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">Login</a>
                <a href="{{ route('register') }}" class="btn btn-light btn-sm">Register</a>
            </div>
        @endauth
    </div>
</nav>

<main class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>