<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Template extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => ucfirst($this->name),
            'version' => $this->version,
            'updated_at' => $this->updated_at,
        ];
    }
}
