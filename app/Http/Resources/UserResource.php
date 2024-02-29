<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'level' => $this->level,
            'role_name' => $this->role->role_name,
            'firstname' => $this->user_detail->first_name,
            'lastname' => $this->user_detail->last_name,
            'phone' => $this->user_detail->phone,
            'status' => $this->user_detail->status,
            'created_at' => $this->user_detail->created_at,
            'updated_at' => $this->user_detail->updated_at,
        ];
    }
}
