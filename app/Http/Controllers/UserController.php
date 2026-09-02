<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna.
     */
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    /**
     * Setujui pendaftaran pengguna.
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'permission' => 'approved'
        ]);

        return back()->with('success', 'Pengguna berhasil disetujui.');
    }

    /**
     * Tolak pendaftaran pengguna.
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'permission' => 'rejected'
        ]);

        return back()->with('success', 'Pengguna berhasil ditolak.');
    }

    /**
     * Pulihkan status pendaftaran pengguna ke pending.
     */
    public function restore($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'permission' => 'pending'
        ]);

        return back()->with('success', 'Status pengguna berhasil dipulihkan ke pending.');
    }

    /**
     * Ubah role pengguna menjadi Admin.
     */
    public function makeAdmin($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'status' => 'admin'
        ]);

        return back()->with('success', 'Status pengguna berhasil diubah menjadi Admin.');
    }

    /**
     * Hapus akun pengguna secara permanen.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Mencegah menghapus akun sendiri
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }
}