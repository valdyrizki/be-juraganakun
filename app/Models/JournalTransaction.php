<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalTransaction extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function journal_account()
    {
        return $this->hasOne(JournalAccount::class,'id','journal_account_id');
    }
}
