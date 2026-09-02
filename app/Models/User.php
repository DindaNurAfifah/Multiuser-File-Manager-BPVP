<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // Tambahkan baris ini untuk mematikan timestamps otomatis
    public $timestamps = false;

    protected $fillable = [
        'name',
        'bagian',
        'email',
        'username',
        'password',
        'status',
        'permission',
    ];

    public function isAdmin()
    {
        // Sesuaikan 'admin' dengan nama nilai kolom status/role di database kamu
        return $this->status === 'admin'; 
    }
}