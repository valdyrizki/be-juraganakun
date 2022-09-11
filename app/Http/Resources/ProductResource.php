<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'stock' => $this->stock,
            'cogs' => $this->cogs,
            'price' => $this->price,
            'description' => $this->description,
            'status' => $this->status,
            'distributor' => $this->distributor,
            'category_id' => $this->category_id,
            'path' => $this->productImage->path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
