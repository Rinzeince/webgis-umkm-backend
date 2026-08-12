<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artikel extends Model
{
    use HasFactory;

    protected $table = 'artikel';
    protected $primaryKey = 'id_artikel';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'thumbnail_url',
        'is_published',
        'id_author',
        'penulis',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get the author of this article.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_author');
    }
}
