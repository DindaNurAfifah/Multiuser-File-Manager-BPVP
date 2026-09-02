<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama & isi folder
     */
    public function index(Request $request, $folderId = null)
    {
        $user = Auth::user();
        $query = $request->input('search');
        $type = $request->input('type');

        $currentFolder = $folderId ? Folder::findOrFail($folderId) : null;

        // Hierarki Breadcrumb Navigation
        $breadcrumbs = [];
        $tempFolder = $currentFolder;
        while ($tempFolder) {
            array_unshift($breadcrumbs, $tempFolder);
            $tempFolder = $tempFolder->parent;
        }

        // 1. Query Base Folder
        $foldersQuery = Folder::with('author')
            ->where('parent_id', $folderId)
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('folder_access', '!=', 'private')
                        ->orWhere('folder_author', $user->id);
                });
            });

        // Filter Nama Folder
        if ($query) {
            $foldersQuery->where('folder_name', 'LIKE', "%{$query}%");
        }

        // 2. Query Base File
        $filesQuery = File::with(['author', 'folder'])
            ->where('folder_id', $folderId)
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('file_access', '!=', 'private')
                        ->orWhere('file_author', $user->id);
                });
            });

        // Filter Nama File
        if ($query) {
            $filesQuery->where('file_name', 'LIKE', "%{$query}%");
        }

        // Filter Format Ekstensi File
        if ($type) {
            $filesQuery->where('file_name', 'LIKE', "%.{$type}");
        }

        $folders = $type ? collect([]) : $foldersQuery->get();
        $files = $filesQuery->get();

        // Recent Files
        $recentFiles = File::where('file_author', $user->id)
            ->orderBy('file_date', 'desc')
            ->take(4)
            ->get();

        return view('dashboard.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs', 'recentFiles'));
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'folder_name' => 'required|string',
            'folder_permission' => 'required|in:Full,View Only,Private',
        ]);

        $access = ($request->folder_permission === 'Private') ? 'private' : 'public';

        Folder::create([
            'parent_id' => $request->parent_id ?: null,
            'folder_name' => $request->folder_name,
            'folder_access' => $access,
            'folder_permission' => $request->folder_permission,
            'folder_author' => Auth::id(),
            'folder_date' => now(),
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'file_permission' => 'required|in:Full,View Only,Private',
        ]);

        $uploadedFile = $request->file('file');
        $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
        $uploadedFile->storeAs('uploads', $fileName, 'public');

        $access = ($request->file_permission === 'Private') ? 'private' : 'public';

        File::create([
            'file_name' => $fileName,
            'file_access' => $access,
            'file_permission' => $request->file_permission,
            'file_author' => Auth::id(),
            'folder_id' => $request->folder_id ?: null,
            'file_folder' => $request->folder_id ?: null,
            'file_date' => now(),
        ]);

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function uploadZip(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip',
            'permission' => 'required|in:Full,View Only,Private',
        ]);

        $file = $request->file('zip_file');
        $zip = new ZipArchive();

        $access = ($request->permission === 'Private') ? 'private' : 'public';

        if ($zip->open($file->getRealPath()) === TRUE) {
            $extractFolder = 'uploads/extracted_' . time();
            $extractPath = storage_path('app/public/' . $extractFolder);
            $zip->extractTo($extractPath);

            $newFolder = Folder::create([
                'parent_id' => $request->folder_id ?: null,
                'folder_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'folder_access' => $access,
                'folder_permission' => $request->permission,
                'folder_author' => Auth::id(),
                'folder_date' => now(),
            ]);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (!$stat['size']) continue;

                File::create([
                    'file_name' => basename($stat['name']),
                    'file_access' => $access,
                    'file_permission' => $request->permission,
                    'file_author' => Auth::id(),
                    'folder_id' => $newFolder->id,
                    'file_folder' => $newFolder->id,
                    'file_date' => now(),
                ]);
            }
            $zip->close();
        }

        return back()->with('success', 'Arsip ZIP berhasil diunggah dan diekstrak.');
    }

    public function downloadFile($id)
    {
        $file = File::findOrFail($id);

        if ($file->file_permission === 'View Only' && Auth::id() !== $file->file_author && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Akses ditolak. File ini berstatus View Only.');
        }

        return Storage::disk('public')->download('uploads/' . $file->file_name);
    }

    public function deleteFile($id)
    {
        $file = File::findOrFail($id);

        if (Auth::user()->isAdmin() || Auth::id() === $file->file_author) {
            Storage::disk('public')->delete('uploads/' . $file->file_name);
            $file->delete();
            return back()->with('success', 'File berhasil dihapus.');
        }

        return back()->with('error', 'Anda tidak memiliki hak akses untuk menghapus file ini.');
    }

    public function updateFolderPermission(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        if (!Auth::user()->isAdmin() && Auth::id() !== $folder->folder_author) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah folder ini.');
        }

        $request->validate([
            'folder_permission' => 'required|in:Full,View Only,Private',
        ]);

        $access = ($request->folder_permission === 'Private') ? 'private' : 'public';

        $folder->update([
            'folder_access' => $access,
            'folder_permission' => $request->folder_permission,
        ]);

        return back()->with('success', 'Hak akses folder berhasil diperbarui.');
    }

    public function updateFilePermission(Request $request, $id)
    {
        $file = File::findOrFail($id);

        if (!Auth::user()->isAdmin() && Auth::id() !== $file->file_author) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah file ini.');
        }

        $request->validate([
            'file_permission' => 'required|in:Full,View Only,Private',
        ]);

        $access = ($request->file_permission === 'Private') ? 'private' : 'public';

        $file->update([
            'file_access' => $access,
            'file_permission' => $request->file_permission,
        ]);

        return back()->with('success', 'Hak akses file berhasil diperbarui.');
    }

    public function myFiles(Request $request, $folderId = null)
    {
        $user = Auth::user();
        $query = $request->input('search');
        $type = $request->input('type');

        $currentFolder = $folderId ? Folder::where('id', $folderId)->where('folder_author', $user->id)->firstOrFail() : null;

        $breadcrumbs = [];
        $tempFolder = $currentFolder;
        while ($tempFolder) {
            array_unshift($breadcrumbs, $tempFolder);
            $tempFolder = $tempFolder->parent ? Folder::where('id', $tempFolder->parent_id)->where('folder_author', $user->id)->first() : null;
        }

        $foldersQuery = Folder::with('author')
            ->where('parent_id', $folderId)
            ->where('folder_author', $user->id);

        if ($query) {
            $foldersQuery->where('folder_name', 'LIKE', "%{$query}%");
        }

        $filesQuery = File::with(['author', 'folder'])
            ->where('folder_id', $folderId)
            ->where('file_author', $user->id);

        if ($query) {
            $filesQuery->where('file_name', 'LIKE', "%{$query}%");
        }

        if ($type) {
            $filesQuery->where('file_name', 'LIKE', "%.{$type}");
        }

        $folders = $type ? collect([]) : $foldersQuery->get();
        $files = $filesQuery->get();

        $recentFiles = File::where('file_author', $user->id)
            ->orderBy('file_date', 'desc')
            ->take(4)
            ->get();

        return view('dashboard.my-files', compact('folders', 'files', 'currentFolder', 'breadcrumbs', 'recentFiles'));
    }

    public function previewFile($id)
    {
        $file = File::findOrFail($id);

        // Cek hak akses jika file bersifat Private
        if ($file->file_access === 'private' && Auth::id() !== $file->file_author && !Auth::user()->isAdmin()) {
            return back()->with('error', 'Akses ditolak. File ini bersifat privat.');
        }

        $filePath = storage_path('app/public/uploads/' . $file->file_name);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));

        // SEMUA FORMAT (PDF, Gambar, DOCX, XLSX) DIARAHKAN KE VIEW PREVIEW YANG SAMA
        return view('dashboard.preview', compact('file', 'extension'));
    }

    public function rawFile($id)
    {
        $file = File::findOrFail($id);

        if ($file->file_access === 'private' && Auth::id() !== $file->file_author && !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak');
        }

        $filePath = storage_path('app/public/uploads/' . $file->file_name);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        // Return file dengan Content-Disposition inline agar tidak langsung terdownload
        return response()->file($filePath, [
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"'
        ]);
    }
}