<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'file_name', 
        'file_date', 
        'file_access', 
        'file_permission', 
        'file_author', 
        'folder_id', 
        'file_folder'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'file_author');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }
}