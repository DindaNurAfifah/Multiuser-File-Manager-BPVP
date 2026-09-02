<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

// ==========================================
// 1. RUTE GUEST (Belum Login)
// ==========================================
Route::middleware('guest')->group(function () {
    // Auth Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Auth Register
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// 2. RUTE AUTHENTICATED (Sudah Login)
// ==========================================
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --------------------------------------
    // Dashboard & Navigasi Folder
    // --------------------------------------
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/folder/{folderId}', [DashboardController::class, 'index'])->name('dashboard.folder');
    Route::get('/files/preview/{id}', [DashboardController::class, 'previewFile'])->name('files.preview');

    // --------------------------------------
    // Operasi Folder
    // --------------------------------------
    Route::post('/folders/create', [DashboardController::class, 'createFolder'])->name('folders.create');
    Route::put('/folders/{id}/permission', [DashboardController::class, 'updateFolderPermission'])->name('folders.updatePermission');

    // --------------------------------------
    // Preview File
    // --------------------------------------
    Route::get('/files/raw/{id}', [DashboardController::class, 'rawFile'])->name('files.raw');
    Route::get('/files/preview/{id}', [DashboardController::class, 'previewFile'])->name('files.preview');

    // --------------------------------------
    // Operasi File
    // --------------------------------------
    Route::post('/files/upload', [DashboardController::class, 'uploadFile'])->name('files.upload');
    Route::post('/files/upload-zip', [DashboardController::class, 'uploadZip'])->name('files.uploadZip');
    Route::get('/files/download/{id}', [DashboardController::class, 'downloadFile'])->name('files.download');
    Route::put('/files/{id}/permission', [DashboardController::class, 'updateFilePermission'])->name('files.updatePermission');
    Route::delete('/files/delete/{id}', [DashboardController::class, 'deleteFile'])->name('files.delete');

    // --------------------------------------
    // My File
    // --------------------------------------
    
    Route::get('/my-files', [DashboardController::class, 'myFiles'])->name('my-files');
    Route::get('/my-files/folder/{folderId}', [DashboardController::class, 'myFiles'])->name('my-files.folder');

    // --------------------------------------
    // Manajemen Pengguna (Khusus Admin)
    // --------------------------------------
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::post('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::patch('/users/{id}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::patch('/users/{id}/reject', [UserController::class, 'reject'])->name('users.reject');
        Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::patch('/users/{id}/make-admin', [UserController::class, 'makeAdmin'])->name('users.makeAdmin');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});