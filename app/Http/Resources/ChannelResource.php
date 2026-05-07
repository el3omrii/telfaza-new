<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChannelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'logo' => $this->logo,
            'image' => $this->image,
            'language' => $this->language,
            'quality' => $this->quality,
            'epgid' => $this->epgid,
            'featured' => (bool) $this->featured,
            'views' => $this->views,
            'country_id' => $this->country_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sources_count' => $this->sources_count,
            /*'country' => new CountryResource($this->whenLoaded('country')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),*/
        ];
    }
}
