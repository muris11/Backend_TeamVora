<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TeamMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $filePath = $this->file_path;
        $url = null;

        if ($filePath) {
            // If stored as full URL (old format), extract path
            $s3Url = config('filesystems.disks.s3.url');
            if ($s3Url && str_starts_with($filePath, $s3Url)) {
                $filePath = str_replace($s3Url . '/', '', $filePath);
            }

            // If stored as relative path, generate temporary URL
            try {
                $url = Storage::disk('s3')->temporaryUrl($filePath, now()->addHour());
            } catch (\Exception $e) {
                $url = $filePath;
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
