<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'customer' => $this->customer,
            'body' => $this->review,
            'star' => $this->star,
            // "href"=> [
            //     "link" =>route('reviews.show',$this->id,$this->id)
            // ]
        ];
        // return parent::toArray($request);
    }
}
