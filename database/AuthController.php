$phpCode = @'
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'required|string|unique:users,student_id',
            'department' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'student_id' => $request->student_id,
            'department' => $request->department,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        Auth::login($user);

        return redirect()->route('student.dashboard');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $role = Auth::user()->role;

            return match ($role) {
                'admin' => redirect()->route('admin.dashboard'),
                'department' => redirect()->route('department.dashboard'),
                default => redirect()->route('student.dashboard'),
            };
        }

        return back()->withErrors([
            'email' => 'ያስገቡት ኢሜይል ወይም የይለፍ ቃል ትክክል አይደለም።',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
'@

$fullPath = Join-Path (Get-Location).Path "app\Http\Controllers\AuthController.php"
[System.IO.File]::WriteAllText($fullPath, $phpCode, [System.Text.UTF8Encoding]::new($false))