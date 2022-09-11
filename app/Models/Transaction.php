<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $incrementing = false;
    protected $primaryKey = 'invoice_id';

    public function transaction_detail()
    {
        return $this->hasMany(TransactionDetail::class,'invoice_id','invoice_id');
    }

    public function product_files()
    {
        return $this->hasMany(ProductFile::class,'invoice_id','invoice_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class,'invoice_id','invoice_id')->where('status',1);
    }
}
