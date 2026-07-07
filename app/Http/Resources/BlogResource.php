<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'author_id' => $this->author_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'status' => $this->status,
            'featured_image' => $this->featured_image,
            'published_at' => $this->published_at?->toISOString(),
            'focus_keyword' => $this->focus_keyword,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_keywords' => $this->seo_keywords ?? [],
            'canonical_url' => $this->canonical_url,
            'og_image' => $this->og_image,
            'twitter_card' => $this->twitter_card,
            'author' => new UserResource($this->whenLoaded('author')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
