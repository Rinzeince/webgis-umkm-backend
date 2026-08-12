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
            $path = preg_replace('/^https?:\/\/[^\/]+\/storage\//', '', $thumbnail);
            $path = str_replace('/storage/', '', $path);
            $path = ltrim($path, '/');
            
            $baseUrl = $request->getSchemeAndHttpHost();
            if (!str_contains($baseUrl, ':8000') && (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1'))) {
                $baseUrl = 'http://127.0.0.1:8000';
            }
            $thumbnail = $baseUrl . '/storage/' . $path;
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
            'published_at' => $this->published_at?->translatedFormat('d M Y') ?? $this->created_at?->translatedFormat('d M Y'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
