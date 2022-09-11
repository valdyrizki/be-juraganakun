<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $incrementing = false;
    protected $primaryKey = 'product_id';

    public function productImage()
    {
        return $this->hasOne(ProductImage::class,'product_id','product_id');
    }

    public function transaction_detail()
    {
        return $this->belongsTo(TransactionDetail::class);
    }
}
