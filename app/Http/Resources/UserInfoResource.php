<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class UserInfoResource extends JsonResource
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
            'sub' => $this->sub,
            'email' => $this->email,
            'name' => $this->name,
            'roles' => $this->getRoleNames(),
            'created_at' => Carbon::parse($this->created_at)->toIso8601String(),
            'updated_at' => Carbon::parse($this->updated_at)->toIso8601String(),
        ];
    }
}
