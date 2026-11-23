<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Approval Dashboard - Admas Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admas Clearance - Staff Panel</a>
            <form method="POST" action="{{ route('logout') }}" class="d-flex">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container py-5">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Student Clearance Requests</div>
            <div class="card-body table-responsive">
                @if ($requests->isEmpty())
                    <p class="text-muted mb-0">No clearance requests found.</p>
                @else
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Reason</th>
                                <th>Library</th>
                                <th>Department</th>
                                <th>Finance</th>
                                <th>Registrar</th>
                                <th>Overall</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $req)
                                <tr>
                                    <td>
                                        <strong>{{ $req->user->name }}</strong><br>
                                        <small class="text-muted">{{ $req->user->student_id }} | {{ $req->user->department }}</small>
                                    </td>
                                    <td>{{ $req->reason }}</td>
                                    @foreach (['library_status', 'department_status', 'finance_status', 'registrar_status'] as $office)
                                        <td>
                                            <div class="mb-1">
                                                <span class="badge bg-{{ $req->$office === 'approved' ? 'success' : ($req->$office === 'rejected' ? 'danger' : 'warning text-dark') }}">
                                                    {{ ucfirst($req->$office) }}
                                                </span>
                                            </div>
                                            <form method="POST" action="{{ route('clearance.updateStatus', $req) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="office" value="{{ $office }}">
                                                <button name="status" value="approved" class="btn btn-success btn-sm py-0 px-1">✓</button>
                                                <button name="status" value="rejected" class="btn btn-danger btn-sm py-0 px-1">✕</button>
                                            </form>
                                        </td>
                                    @endforeach
                                    <td>
                                        <span class="badge bg-{{ $req->overall_status === 'approved' ? 'success' : ($req->overall_status === 'rejected' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($req->overall_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</body>
</html>