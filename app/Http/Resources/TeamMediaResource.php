<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $filePath = $this->file_path;
        $url = null;

        if ($filePath) {
            $cdnBase = rtrim(config('filesystems.disks.r2.url', 'https://' . env('R2_CUSTOM_DOMAIN')), '/');


            if (str_starts_with($filePath, 'https://') || str_starts_with($filePath, 'http://')) {
                $url = $filePath;
            } else {
                $url = $cdnBase . '/' . ltrim($filePath, '/');
            }
        }


        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'type' => $this->type,
            'name' => $this->name,
            'file_path' => $url,
            'size' => (int) $this->size,
            'mime_type' => $this->mime_type,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
