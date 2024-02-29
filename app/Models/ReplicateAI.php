<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplicateAI extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $table = 'replicate_ai';
}
