<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip - File Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden text-gray-700">

    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between">
        <div>
            <!-- Logo -->
            <div class="p-6 flex items-center gap-3">
                <span class="font-bold text-xl text-gray-800">BPVP</span>
            </div>

            <!-- Global Search -->
            <div class="px-4 mb-4">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400 text-sm"></i>
                    <input type="text" placeholder="Search..." class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none">
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="px-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-3 py-2.5 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg font-medium text-sm">
                    <div class="flex items-center gap-3"><i class="fa-solid fa-house"></i> Home</div>
                    <!-- <span class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full text-xs font-semibold">10</span> -->
                </a>
                <a href="{{ route('my-files') }}"  class="flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg font-medium text-sm">
                    <i class="fa-solid fa-folder"></i> My File
                </a>

                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.users') }}" class="flex items-center justify-between px-3 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg font-medium text-sm">
                    <div class="flex items-center gap-3"><i class="fa-solid fa-user"></i> Users</div>
                    <!-- <span class="bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full text-xs font-semibold">Admin</span> -->
                </a>
                @else
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-100 rounded-lg font-medium text-sm">
                    <i class="fa-regular fa-comment-dots"></i> Help & Support
                </a>
                @endif
            </nav>
        </div>

        <!-- Sidebar Footer / Profile -->
        <div class="p-4 border-t border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gray-300 rounded-full flex items-center justify-center font-bold text-gray-600">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ Auth::user()->status }} ({{ Auth::user()->bagian }})</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-500 p-1"><i class="fa-solid fa-right-from-bracket"></i></button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto p-8">
        @yield('content')
    </main>

</body>
</html>