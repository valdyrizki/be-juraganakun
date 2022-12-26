<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalTransactionResource extends JsonResource
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
            'txid' => $this->txid,
            'journal_account_id' => $this->journal_account_id,
            'journal_account' => $this->journal_account,
            'dbcr' => $this->dbcr,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
