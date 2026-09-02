@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" onclick="closeContextMenu()">

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

    <!-- Breadcrumb Navigation & Back Button -->
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-2 text-xl font-bold text-gray-800">
            <a href="{{ route('dashboard') }}" class="hover:underline text-indigo-600">Home</a>
            @foreach($breadcrumbs as $crumb)
                <span class="text-gray-400 font-normal">/</span>
                <a href="{{ route('dashboard.folder', $crumb->id) }}" class="hover:underline text-gray-700">{{ $crumb->folder_name }}</a>
            @endforeach
        </nav>
        
        @if($currentFolder)
            <a href="{{ $currentFolder->parent_id ? route('dashboard.folder', $currentFolder->parent_id) : route('dashboard') }}" class="px-3.5 py-1.5 text-xs font-semibold text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        @endif
    </div>

    <!-- Search & Filter Bar -->
    <form action="" method="GET" class="flex items-center gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search files and folders..." class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
        </div>
        <select name="type" onchange="this.form.submit()" class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-sm text-gray-600 focus:outline-none">
            <option value="">Semua Format</option>
            <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
            <option value="docx" {{ request('type') == 'docx' ? 'selected' : '' }}>Word (DOCX)</option>
            <option value="xlsx" {{ request('type') == 'xlsx' ? 'selected' : '' }}>Excel (XLSX)</option>
            <option value="pptx" {{ request('type') == 'pptx' ? 'selected' : '' }}>PowerPoint (PPTX)</option>
            <option value="jpg" {{ request('type') == 'jpg' ? 'selected' : '' }}>Gambar (JPG/PNG)</option>
        </select>
    </form>

    <!-- Recent Files Section -->
    <div>
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Recent Files</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($recentFiles as $recent)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between h-32">
                <div class="flex items-center gap-3">
                    <div class="text-2xl">
                        @if(Str::endsWith($recent->file_name, '.pdf'))
                            <i class="fa-regular fa-file-pdf text-rose-500"></i>
                        @elseif(Str::endsWith($recent->file_name, ['.doc', '.docx']))
                            <i class="fa-regular fa-file-word text-blue-500"></i>
                        @elseif(Str::endsWith($recent->file_name, ['.xls', '.xlsx']))
                            <i class="fa-regular fa-file-excel text-emerald-500"></i>
                        @else
                            <i class="fa-regular fa-file-lines text-gray-400"></i>
                        @endif
                    </div>
                    <a href="{{ route('files.preview', $recent->id) }}" class="text-xs font-medium text-gray-700 hover:text-indigo-600 truncate w-full" title="{{ $recent->file_name }}">
                        {{ $recent->file_name }}
                    </a>
                </div>
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-50">
                    <span class="text-[10px] text-gray-400">{{ $recent->file_date }}</span>
                    <a href="{{ route('files.download', $recent->id) }}" class="text-gray-400 hover:text-indigo-600 text-xs" title="Unduh File">
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 col-span-4">Belum ada berkas terbaru.</p>
            @endforelse
        </div>
    </div>

    <!-- Toolbar Action Header -->
    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <div class="flex items-center gap-2">
            <h3 class="text-base font-bold text-gray-800">Shared Files</h3>
            <span class="bg-indigo-50 text-indigo-600 text-xs px-2.5 py-0.5 rounded-full font-semibold">{{ count($files) + count($folders) }} Item</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('folderModal').classList.remove('hidden')" class="px-3 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 text-sm font-medium flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-folder-plus text-indigo-600"></i> Folder Baru
            </button>
            <button onclick="openUploadModal('single')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Upload 
            </button>
        </div>
    </div>

    <!-- Data Table Penampil File & Folder -->
    <div oncontextmenu="showGlobalContextMenu(event)" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[300px]">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 bg-gray-50/50 select-none">
                    <th class="py-3.5 px-6">Name</th>
                    <th class="py-3.5 px-6">Author & Bagian</th>
                    <th class="py-3.5 px-6">Last Modified</th>
                    <th class="py-3.5 px-6">Permission</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <!-- List Folders -->
                @foreach($folders as $folder)
                <tr oncontextmenu="showFolderContextMenu(event, {{ $folder->id }}, '{{ $folder->folder_permission }}', {{ (Auth::user()->isAdmin() || Auth::id() === $folder->folder_author) ? 'true' : 'false' }})" class="hover:bg-gray-50/70 transition-colors cursor-pointer select-none">
                    <td class="py-4 px-6 font-medium text-gray-800">
                        <a href="{{ route('dashboard.folder', $folder->id) }}" class="flex items-center gap-3 hover:text-indigo-600 group">
                            <i class="fa-solid fa-folder text-indigo-500 group-hover:scale-110 transition-transform text-lg"></i>
                            <span>{{ $folder->folder_name }}</span>
                        </a>
                    </td>
                    <td class="py-4 px-6 text-gray-500 text-xs">{{ $folder->author->name }} ({{ $folder->author->bagian }})</td>
                    <td class="py-4 px-6 text-gray-500 text-xs">{{ $folder->folder_date }}</td>
                    <td class="py-4 px-6">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600">{{ $folder->folder_permission }}</span>
                    </td>
                </tr>
                @endforeach

                <!-- List Files -->
                @foreach($files as $file)
                <tr oncontextmenu="showFileContextMenu(event, {{ $file->id }}, '{{ $file->file_permission }}', {{ (Auth::user()->isAdmin() || Auth::id() === $file->file_author) ? 'true' : 'false' }})" class="hover:bg-gray-50/70 transition-colors cursor-pointer select-none">
                    <td class="py-4 px-6 font-medium text-gray-800 flex items-center gap-3">
                        @if(Str::endsWith($file->file_name, '.pdf'))
                            <i class="fa-solid fa-file-pdf text-rose-500 text-lg"></i>
                        @elseif(Str::endsWith($file->file_name, ['.doc', '.docx']))
                            <i class="fa-solid fa-file-word text-blue-500 text-lg"></i>
                        @elseif(Str::endsWith($file->file_name, ['.xls', '.xlsx']))
                            <i class="fa-solid fa-file-excel text-emerald-500 text-lg"></i>
                        @else
                            <i class="fa-solid fa-file text-gray-400 text-lg"></i>
                        @endif
                        <a href="{{ route('files.preview', $file->id) }}" class="hover:text-indigo-600 hover:underline">
                            {{ $file->file_name }}
                        </a>
                    </td>
                    <td class="py-4 px-6 text-gray-500 text-xs">{{ $file->author->name }} ({{ $file->author->bagian }})</td>
                    <td class="py-4 px-6 text-gray-500 text-xs">{{ $file->file_date }}</td>
                    <td class="py-4 px-6">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-600">{{ $file->file_permission }}</span>
                    </td>
                </tr>
                @endforeach

                @if(count($folders) === 0 && count($files) === 0)
                <tr>
                    <td colspan="4" class="py-12 text-center text-sm text-gray-400">
                        Tidak ada berkas atau folder ditemukan. Klik kanan di mana saja untuk membuat folder atau upload file.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>

<!-- Custom Right Click Context Menu -->
<div id="contextMenu" class="hidden fixed w-52 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 z-50 text-xs">
    <a id="ctxOpenFolder" href="#" class="px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2.5 font-medium">
        <i class="fa-solid fa-folder-open text-indigo-500"></i> Buka Folder
    </a>
    <a id="ctxOpenFile" href="#" class="px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2.5 font-medium">
        <i class="fa-solid fa-eye text-indigo-500"></i> Buka File
    </a>
    <a id="ctxDownloadFile" href="#" class="px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2.5 font-medium">
        <i class="fa-solid fa-download text-indigo-500"></i> Unduh File
    </a>
    <button id="ctxEditPermission" type="button" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-amber-50 hover:text-amber-600 flex items-center gap-2.5 font-medium">
        <i class="fa-solid fa-user-gear text-amber-500"></i> Ubah Hak Akses
    </button>
    <form id="ctxDeleteForm" method="POST">
        @csrf
        @method('DELETE')
        <button id="ctxDeleteFile" type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus berkas ini?')" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 flex items-center gap-2.5 font-medium">
            <i class="fa-solid fa-trash"></i> Hapus File
        </button>
    </form>

    <hr id="ctxDivider" class="my-1 border-gray-100">

    <button id="ctxCreateFolder" type="button" onclick="openModal('folderModal')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2.5 font-medium">
        <i class="fa-solid fa-folder-plus text-indigo-600"></i> Folder Baru
    </button>
    <button id="ctxUploadFile" type="button" onclick="openUploadModal('single')" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 flex items-center gap-2.5 font-medium">
        <i class="fa-solid fa-file-circle-plus text-emerald-600"></i> Upload File / ZIP
    </button>
</div>

<!-- Modal 1: Buat Folder Baru -->
<div id="folderModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
        <h4 class="font-bold text-lg mb-4 text-gray-800">Buat Folder Baru</h4>
        <form action="{{ route('folders.create') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Folder</label>
                <input type="text" name="folder_name" placeholder="Masukkan nama folder" required class="w-full p-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Permission</label>
                <select name="folder_permission" class="w-full p-2.5 border rounded-lg text-sm">
                    <option value="Full">Full</option>
                    <option value="View Only">View Only</option>
                    <option value="Private">Private</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('folderModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Combined Upload Modal -->
<div id="combinedUploadModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-100">
        <h4 class="font-bold text-lg mb-4 text-gray-800">Upload Berkas</h4>
        
        <div class="flex gap-2 p-1 bg-gray-100/80 rounded-xl mb-4 text-xs font-semibold">
            <button type="button" id="tabSingle" onclick="switchUploadMode('single')" class="flex-1 py-2 text-center rounded-lg bg-white text-indigo-600 shadow-sm transition-all">
                <i class="fa-solid fa-file mr-1"></i> File Standar
            </button>
            <button type="button" id="tabZip" onclick="switchUploadMode('zip')" class="flex-1 py-2 text-center rounded-lg text-gray-500 hover:text-gray-800 transition-all">
                <i class="fa-solid fa-file-zipper text-amber-500 mr-1"></i> File ZIP (Ekstrak)
            </button>
        </div>

        <form id="combinedUploadForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="folder_id" id="upload_folder_id" value="{{ $currentFolder->id ?? '' }}">

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Pilih Berkas</label>
                <div id="dropZone" class="relative border-2 border-dashed border-gray-300 rounded-2xl p-6 transition-all duration-200 text-center bg-gray-50/50 hover:bg-gray-100/50 hover:border-indigo-400 group cursor-pointer">
                    <input type="file" id="fileInput" name="file" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="handleFileSelect(this)">
                    
                    <div class="space-y-2 pointer-events-none" id="dropZoneContent">
                        <div class="w-12 h-12 bg-indigo-50 group-hover:bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto transition-colors">
                            <i id="dropZoneIcon" class="fa-solid fa-cloud-arrow-up text-xl"></i>
                        </div>
                        <div class="text-xs text-gray-600">
                            <span class="font-semibold text-indigo-600 hover:underline">Klik untuk mengunggah</span> atau seret file ke sini
                        </div>
                        <p id="sizeLimitInfo" class="text-[11px] text-gray-400">Mendukung format dokumen & gambar hingga 20MB</p>
                    </div>

                    <div id="fileSelectedPreview" class="hidden flex items-center justify-between bg-white p-3 rounded-xl border border-gray-200 pointer-events-auto">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                                <i id="previewFileIcon" class="fa-solid fa-file text-indigo-600"></i>
                            </div>
                            <div class="text-left overflow-hidden">
                                <p id="previewFileName" class="text-xs font-semibold text-gray-700 truncate">filename.pdf</p>
                                <p id="previewFileSize" class="text-[10px] text-gray-400">0 KB</p>
                            </div>
                        </div>
                        <button type="button" onclick="resetFileSelection(event)" class="text-gray-400 hover:text-rose-500 text-sm p-1">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Permission</label>
                <select name="file_permission" id="upload_permission" class="w-full p-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="Full">Full</option>
                    <option value="View Only">View Only</option>
                    <option value="Private">Private</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 font-medium">Batal</button>
                <button type="submit" id="submitUploadBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-upload text-xs"></i> <span>Upload</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit File Permission -->
<div id="editFileModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
        <h4 class="font-bold text-lg mb-4 text-gray-800">Ubah Hak Akses File</h4>
        <form id="editFileForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Permission</label>
                <select name="file_permission" id="edit_file_permission" class="w-full p-2.5 border rounded-lg text-sm">
                    <option value="Full">Full</option>
                    <option value="View Only">View Only</option>
                    <option value="Private">Private</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('editFileModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Folder Permission -->
<div id="editFolderModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
        <h4 class="font-bold text-lg mb-4 text-gray-800">Ubah Hak Akses Folder</h4>
        <form id="editFolderForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Permission</label>
                <select name="folder_permission" id="edit_folder_permission" class="w-full p-2.5 border rounded-lg text-sm">
                    <option value="Full">Full</option>
                    <option value="View Only">View Only</option>
                    <option value="Private">Private</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('editFolderModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const contextMenu = document.getElementById('contextMenu');
    const ctxOpenFolder = document.getElementById('ctxOpenFolder');
    const ctxOpenFile = document.getElementById('ctxOpenFile');
    const ctxDownloadFile = document.getElementById('ctxDownloadFile');
    const ctxEditPermission = document.getElementById('ctxEditPermission');
    const ctxDeleteForm = document.getElementById('ctxDeleteForm');
    const ctxDivider = document.getElementById('ctxDivider');
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    function showContextMenu(e) {
        e.preventDefault();
        e.stopPropagation();

        contextMenu.style.display = 'block';
        const menuWidth = contextMenu.offsetWidth;
        const menuHeight = contextMenu.offsetHeight;

        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        let x = e.clientX;
        let y = e.clientY;

        if (x + menuWidth > windowWidth) {
            x = windowWidth - menuWidth - 10;
        }

        if (y + menuHeight > windowHeight) {
            y = y - menuHeight;
            if (y < 0) y = 10;
        }

        contextMenu.style.left = `${x}px`;
        contextMenu.style.top = `${y}px`;
        contextMenu.classList.remove('hidden');
    }

    function closeContextMenu() {
        contextMenu.classList.add('hidden');
    }

    function showGlobalContextMenu(e) {
        showContextMenu(e);
        ctxOpenFolder.classList.add('hidden');
        ctxOpenFile.classList.add('hidden');
        ctxDownloadFile.classList.add('hidden');
        ctxEditPermission.classList.add('hidden');
        ctxDeleteForm.classList.add('hidden');
        ctxDivider.classList.add('hidden');
    }

    function showFolderContextMenu(e, id, permission, isOwnerOrAdmin) {
        showContextMenu(e);
        ctxOpenFolder.classList.remove('hidden');
        ctxOpenFolder.href = '/folder/' + id;
        ctxOpenFile.classList.add('hidden');
        ctxDownloadFile.classList.add('hidden');
        ctxDeleteForm.classList.add('hidden');

        if (isOwnerOrAdmin) {
            ctxEditPermission.classList.remove('hidden');
            ctxEditPermission.onclick = function() {
                closeContextMenu();
                openEditFolderModal(id, permission);
            };
        } else {
            ctxEditPermission.classList.add('hidden');
        }
        ctxDivider.classList.remove('hidden');
    }

    function showFileContextMenu(e, id, permission, isOwnerOrAdmin) {
        showContextMenu(e);
        ctxOpenFolder.classList.add('hidden');

        ctxOpenFile.classList.remove('hidden');
        ctxOpenFile.href = '/files/preview/' + id;

        ctxDownloadFile.classList.remove('hidden');
        ctxDownloadFile.href = '/files/download/' + id;

        if (isOwnerOrAdmin) {
            ctxEditPermission.classList.remove('hidden');
            ctxEditPermission.onclick = function() {
                closeContextMenu();
                openEditFileModal(id, permission);
            };
            ctxDeleteForm.classList.remove('hidden');
            ctxDeleteForm.action = '/files/delete/' + id;
        } else {
            ctxEditPermission.classList.add('hidden');
            ctxDeleteForm.classList.add('hidden');
        }
        ctxDivider.classList.remove('hidden');
    }

    function openModal(modalId) {
        closeContextMenu();
        document.getElementById(modalId).classList.remove('hidden');
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50/50');
        }, false);
    });

    ['dragleave'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50/50');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50/50');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(fileInput);
        }
    }, false);

    function handleFileSelect(input) {
        const file = input.files[0];
        if (file) {
            document.getElementById('dropZoneContent').classList.add('hidden');
            const preview = document.getElementById('fileSelectedPreview');
            preview.classList.remove('hidden');

            document.getElementById('previewFileName').innerText = file.name;
            document.getElementById('previewFileSize').innerText = formatBytes(file.size);

            const iconElement = document.getElementById('previewFileIcon');
            if (file.name.endsWith('.pdf')) {
                iconElement.className = 'fa-solid fa-file-pdf text-rose-500';
            } else if (file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
                iconElement.className = 'fa-solid fa-file-word text-blue-500';
            } else if (file.name.endsWith('.xls') || file.name.endsWith('.xlsx')) {
                iconElement.className = 'fa-solid fa-file-excel text-emerald-500';
            } else if (file.name.endsWith('.zip')) {
                iconElement.className = 'fa-solid fa-file-zipper text-amber-500';
            } else {
                iconElement.className = 'fa-solid fa-file text-gray-500';
            }
        }
    }

    function resetFileSelection(event) {
        if(event) event.stopPropagation();
        fileInput.value = '';
        document.getElementById('dropZoneContent').classList.remove('hidden');
        document.getElementById('fileSelectedPreview').classList.add('hidden');
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function openUploadModal(mode = 'single') {
        closeContextMenu();
        switchUploadMode(mode);
        document.getElementById('combinedUploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        resetFileSelection();
        document.getElementById('combinedUploadModal').classList.add('hidden');
    }

    function switchUploadMode(mode) {
        resetFileSelection();
        const form = document.getElementById('combinedUploadForm');
        const tabSingle = document.getElementById('tabSingle');
        const tabZip = document.getElementById('tabZip');
        const submitBtn = document.getElementById('submitUploadBtn');
        const permissionSelect = document.getElementById('upload_permission');
        const dropZoneIcon = document.getElementById('dropZoneIcon');
        const sizeLimitInfo = document.getElementById('sizeLimitInfo');

        if (mode === 'single') {
            form.action = "{{ route('files.upload') }}";
            fileInput.name = "file";
            fileInput.removeAttribute('accept');
            permissionSelect.name = "file_permission";

            dropZoneIcon.className = "fa-solid fa-cloud-arrow-up text-xl";
            sizeLimitInfo.innerText = "Mendukung format dokumen & gambar hingga 20MB";

            tabSingle.className = "flex-1 py-2 text-center rounded-lg bg-white text-indigo-600 shadow-sm transition-all";
            tabZip.className = "flex-1 py-2 text-center rounded-lg text-gray-500 hover:text-gray-800 transition-all";
            submitBtn.className = "px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm flex items-center gap-2";
        } else {
            form.action = "{{ route('files.uploadZip') }}";
            fileInput.name = "zip_file";
            fileInput.setAttribute('accept', '.zip');
            permissionSelect.name = "permission";

            dropZoneIcon.className = "fa-solid fa-file-zipper text-xl text-amber-500";
            sizeLimitInfo.innerText = "Hanya mendukung arsip .ZIP (Maksimal 50MB)";

            tabZip.className = "flex-1 py-2 text-center rounded-lg bg-white text-amber-600 shadow-sm transition-all";
            tabSingle.className = "flex-1 py-2 text-center rounded-lg text-gray-500 hover:text-gray-800 transition-all";
            submitBtn.className = "px-4 py-2 bg-amber-500 text-white rounded-xl text-sm hover:bg-amber-600 transition-colors shadow-sm flex items-center gap-2";
        }
    }

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeContextMenu();
    });

    function openEditFileModal(id, permission) {
        document.getElementById('editFileForm').action = '/files/' + id + '/permission';
        document.getElementById('edit_file_permission').value = permission;
        document.getElementById('editFileModal').classList.remove('hidden');
    }

    function openEditFolderModal(id, permission) {
        document.getElementById('editFolderForm').action = '/folders/' + id + '/permission';
        document.getElementById('edit_folder_permission').value = permission;
        document.getElementById('editFolderModal').classList.remove('hidden');
    }
</script>
@endsection