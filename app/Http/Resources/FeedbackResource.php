<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attachmentUrl = $this->attachment ? asset('storage/' . $this->attachment) : null;
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'attachment' => $attachmentUrl, 
            'username' => $this->user->name,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans()
        ];
    }
}
