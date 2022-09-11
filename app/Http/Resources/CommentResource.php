<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'invoice_id' => $this->invoice_id,
            'comment' => $this->comment,
            'user' => User::find($this->user_create),
            'created_at' => Carbon::create($this->created_at)->toDateTimeString(),
            'updated_at' => Carbon::create($this->updated_at)->toDateTimeString(),
            'comment_files' => $this->comment_files
        ];
    }
}
