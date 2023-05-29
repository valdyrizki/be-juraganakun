<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'invoice_id' => $this->invoice_id,
            'user_id' => $this->user_id,
            'client_name' => $this->client_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'total_price' => $this->total_price,
            'unique_number' => $this->unique_number,
            'discount' => $this->discount,
            'coupon' => $this->coupon,
            'description' => $this->description,
            'bank' => (Auth::user()->level == '99' ? $this->bank : 'QRIS'), //Hanya ditampilkan untuk superuser
            'invoice_merchant' => $this->invoice_merchant,
            'status' => $this->status,
            'eod_id' => $this->eod_id,
            'created_at' => Carbon::create($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::create($this->updated_at)->toDateTimeString(),
            'transaction_details' => new TransactionDetailCollection(TransactionDetailResource::collection($this->transaction_detail)),
            'product_files' => $this->status == 1 || Auth::user()->level == 99 ? $this->product_files : null, //Hanya ditampilkan ketika status sukses (SUPER USER bisa bebas akses)
            'comments' => CommentResource::collection($this->comments),
        ];
    }
}
