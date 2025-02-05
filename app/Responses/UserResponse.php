<?php

namespace App\Responses;
use App\Helpers\JsonResponseHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResponse extends JsonResource
{
    public function toArray($request)
    {
        $formattedPreferences = $this->preferences->isEmpty() ? (object) [] : $this->preferences->keyBy('type')->map(function ($preference) {
            return [
                'id' => $preference->id,
                'user_id' => $preference->user_id,
                'type' => $preference->type,
                'value' => json_decode($preference->value) ?? "",
            ];
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'preferences' => $formattedPreferences,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
