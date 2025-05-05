<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstVariant = $this->variants->first();
        $firstImage = $firstVariant?->images?->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category?->name,
            'image' => $firstImage?->image,
            'variant' => $firstVariant ? [
                'id' => $firstVariant->id,
                'price' => $firstVariant->price,
                'specialPrice' => $firstVariant->specialPrice,
                'discount' => $firstVariant->discount,
            ] : null,
            'average_rating' => $this->reviews && $this->reviews->isNotEmpty() ? round($this->reviews->avg('star'), 1) : 0,
            'total_reviews' => $this->reviews ? $this->reviews->count() : 0,
        ];
    }
}
