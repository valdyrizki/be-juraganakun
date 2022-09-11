<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class,'invoice_id','invoice_id');
    }

    public function comment_files()
    {
        return $this->hasMany(CommentFile::class);
    }
}
