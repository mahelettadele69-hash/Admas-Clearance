<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClearanceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $users = User::latest()->get();
        $logs = AuditLog::with('user')->latest()->take(25)->get();
        $stats = [
            'students' => User::where('role', 'student')->count(),
            'staff' => User::where('role', '!=', 'student')->count(),
            'total_requests' => ClearanceRequest::count(),
            'issued_certs' => ClearanceRequest::whereNotNull('certificate_serial')->count(),
        ];

        return view('admin.dashboard', compact('users', 'logs', 'stats'));
    }

    public function createStaff(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:department_head,library,store,finance,registrar,admin'],
            'department' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'department' => $data['department'],
            'password' => Hash::make($data['password']),
        ]);

        AuditLog::record('Staff Account Created', "Admin created {$user->email} ({$user->role}).");

        return back()->with('success', "Staff account {$user->email} created.");
    }
}