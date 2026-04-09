<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageBuilderSaves extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'title', 'slug', 'template_type', 'template_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
