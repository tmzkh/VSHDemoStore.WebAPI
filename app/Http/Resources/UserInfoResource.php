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
        $createdAt = Carbon::parse($this->created_at)->toIso8601String();

        $updatedAt = Carbon::parse($this->updated_at)->toIso8601String();

        return [
            'id' => $this->id,
            'sub' => $this->sub,
            'email' => $this->email,
            'name' => $this->name,
            'gender' => $this->gender->value(),
            'roles' => $this->getRoleNames(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'is_new_user' => ($updatedAt == $createdAt)
                ? 'true'
                : 'false',
        ];
    }
}
