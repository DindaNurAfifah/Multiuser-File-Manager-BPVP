<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    public $timestamps = false;
    protected $fillable = ['parent_id', 'folder_name', 'folder_date', 'folder_access', 'folder_permission', 'folder_author'];

    public function author()
    {
        return $this->belongsTo(User::class, 'folder_author');
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'folder_id');
    }
}