<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan form login. Satu form dipakai untuk semua role
     * (admin, staff, customer) — pembedaan terjadi setelah login sukses.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login. Redirect ditentukan berdasarkan kolom `role` pada user,
     * bukan berdasarkan form/route yang berbeda.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun Anda sudah dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        // Redirect sesuai role — inti dari "satu login untuk semua role"
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Tampilkan form registrasi (khusus customer).
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi publik. Role di-hardcode 'customer' — tidak boleh
     * diambil dari input form, supaya orang tidak bisa mendaftar sebagai admin.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer', // wajib hardcode, jangan ambil dari $request
            'is_active' => true,
        ]);

        // Buat cart kosong otomatis untuk customer baru
        Cart::create(['user_id' => $user->id]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Registrasi berhasil, selamat datang!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
