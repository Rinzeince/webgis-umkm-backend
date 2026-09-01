<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtikelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $thumbnail = $this->thumbnail_url;
        if ($thumbnail) {
            if (str_starts_with($thumbnail, 'https://') || str_contains($thumbnail, 'amazonaws.com')) {
                // S3 URL or external HTTPS image — keep full URL
            } else {
                $path = preg_replace('/^https?:\/\/[^\/]+\/storage\//', '', $thumbnail);
                $path = str_replace('/storage/', '', $path);
                $path = ltrim($path, '/');
                
                if (config('filesystems.default') === 's3') {
                    $thumbnail = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
                } else {
                    $thumbnail = '/storage/' . $path;
                }
            }
        }

        $content = $this->content;
        if ($content) {
            $content = str_replace('http://localhost/storage/', 'http://127.0.0.1:8000/storage/', $content);
        }

        return [
            'id_artikel' => $this->id_artikel,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $content,
            'thumbnail_url' => $thumbnail,
            'penulis' => $this->penulis ?? $this->author?->name ?? 'Admin',
            'author' => $this->penulis ?? $this->author?->name ?? 'Admin',
            'is_published' => (bool) $this->is_published,
            'published_at' => $this->published_at?->translatedFormat('d M Y') ?? $this->created_at?->translatedFormat('d M Y'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
