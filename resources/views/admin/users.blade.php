@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Kelola Pengguna (User Management)</h2>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    <!-- Data Table User -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-visible">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 bg-gray-50/50 select-none">
                    <th class="py-3.5 px-6">Nama</th>
                    <th class="py-3.5 px-6">Username / Email</th>
                    <th class="py-3.5 px-6">Bagian</th>
                    <th class="py-3.5 px-6">Status</th>
                    <th class="py-3.5 px-6">Permission</th>
                    <th class="py-3.5 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="py-4 px-6 font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="py-4 px-6 text-gray-500 text-xs">
                        <div>{{ $user->username }}</div>
                        <div class="text-gray-400">{{ $user->email }}</div>
                    </td>
                    <td class="py-4 px-6 text-gray-500 text-xs">{{ $user->bagian }}</td>
                    <td class="py-4 px-6">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $user->status === 'admin' ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-100 text-gray-600' }}">
                            {{ strtoupper($user->status) }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-xs">
                        @if($user->status === 'admin')
                            <span class="text-gray-400"></span>
                        @elseif($user->permission === 'approved')
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600">Approved</span>
                        @elseif($user->permission === 'rejected')
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-rose-50 text-rose-600">Rejected</span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-600">Pending</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-center relative">
                        <!-- Dropdown Action Button (Alpine.js) -->
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" type="button" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 focus:outline-none transition-colors">
                                <svg class="w-5 h-5 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>

                            <!-- Menu Dropdown (Mengambang ke atas) -->
                            <div x-show="open" 
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 bottom-full mb-2 w-44 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50 text-left text-xs">
                                
                                {{-- 1. KONDISI PENDING: HANYA MENAMPILKAN TERIMA & REJECT --}}
                                @if($user->permission === 'pending')
                                    <!-- Terima User -->
                                    <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-emerald-600 hover:bg-emerald-50 flex items-center gap-2 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Terima
                                        </button>
                                    </form>

                                    <!-- Reject User -->
                                    <form action="{{ route('admin.users.reject', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menolak pengguna ini?')" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 flex items-center gap-2 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Reject
                                        </button>
                                    </form>

                                {{-- 2. KONDISI REJECTED: MENAMPILKAN PULIHKAN --}}
                                @elseif($user->permission === 'rejected')
                                    <form action="{{ route('admin.users.restore', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-amber-600 hover:bg-amber-50 flex items-center gap-2 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Pulihkan
                                        </button>
                                    </form>

                                {{-- 3. KONDISI APPROVED: HANYA MENAMPILKAN JADIKAN ADMIN (JIKA BUKAN ADMIN) --}}
                                @elseif($user->permission === 'approved' && $user->status !== 'admin')
                                    <form action="{{ route('admin.users.makeAdmin', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" onclick="return confirm('Ubah role pengguna ini menjadi Admin?')" class="w-full text-left px-4 py-2 text-indigo-600 hover:bg-indigo-50 flex items-center gap-2 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            Jadikan Admin
                                        </button>
                                    </form>
                                @endif

                                <!-- 4. HAPUS AKUN (Selalu muncul kecuali untuk akun sendiri) -->
                                @if(Auth::id() !== $user->id)
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus permanen pengguna ini?')" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 flex items-center gap-2 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus Akun
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection