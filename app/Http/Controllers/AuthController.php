<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            AuditLog::record('User Login', Auth::user()->email . ' (' . Auth::user()->role . ') logged in.');
            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users,student_id'],
            'department' => ['required', 'string', 'max:100'],
            'batch' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'student_id' => $data['student_id'],
            'department' => $data['department'],
            'batch' => $data['batch'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'student',
        ]);

        Auth::login($user);
        AuditLog::record('Student Registered', "Student {$user->name} ({$user->student_id}) registered.");

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        AuditLog::record('User Logout', Auth::user()?->email . ' logged out.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function redirectByRole(User $user)
    {
        return match ($user->role) {
            'student' => redirect()->route('student.dashboard'),
            'registrar' => redirect()->route('registrar.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('officer.dashboard'),
        };
    }
}