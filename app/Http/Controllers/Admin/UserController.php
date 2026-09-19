<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Satu halaman list untuk semua role, dibedakan lewat tab/filter role.
     * Ini konsisten dengan keputusan sebelumnya: satu tabel users, tidak
     * dipisah jadi tabel/controller berbeda untuk admin vs customer.
     */
    public function index(Request $request): View
    {
        $role = $request->get('role', 'customer'); // tab default: customer

        $users = User::query()
            ->when($role !== 'all', fn ($q) => $q->where('role', $role))
            ->withCount('orders')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'customer' => User::where('role', 'customer')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'admin' => User::where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact('users', 'role', 'counts'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:admin,staff,customer'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'email_verified_at' => now(), // dibuat manual oleh admin, anggap terverifikasi
        ]);

        if ($validated['role'] === 'customer') {
            Cart::create(['user_id' => $user->id]);
        }

        return redirect()
            ->route('admin.users.index', ['role' => $validated['role']])
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // Cegah admin menonaktifkan/menurunkan role dirinya sendiri secara
        // tidak sengaja — bisa mengunci akses admin dari sistemnya sendiri.
        $isSelf = $user->id === auth()->id();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'role' => [$isSelf ? 'nullable' : 'required', 'in:admin,staff,customer'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        if (! $isSelf) {
            $data['role'] = $validated['role'];
            $data['is_active'] = $request->boolean('is_active');
        }

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index', ['role' => $user->role])
            ->with('success', 'Data pengguna berhasil diperbarui.'
                . ($isSelf ? ' (Role dan status akun sendiri tidak bisa diubah dari sini.)' : ''));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        if ($user->orders()->exists()) {
            return back()->with('error', 'Pengguna tidak bisa dihapus karena memiliki riwayat pesanan. Nonaktifkan akun ini saja.');
        }

        $user->delete(); // soft delete

        return redirect()
            ->route('admin.users.index', ['role' => $user->role])
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
