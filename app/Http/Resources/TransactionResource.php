<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'bank' => $this->bank,
            'status' => $this->status,
            'eod_id' => $this->eod_id,
            'created_at' => Carbon::create($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::create($this->updated_at)->toDateTimeString(),
            'transaction_details' => new TransactionDetailCollection(TransactionDetailResource::collection($this->transaction_detail)),
            'product_files' => $this->product_files,
            'comments' => CommentResource::collection($this->comments),
        ];
    }
}
