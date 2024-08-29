<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserItem extends Model
{
    protected $fillable = ['user_id', 'post_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

