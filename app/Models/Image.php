<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Image extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'path',
        'alt_text',
        'caption',
        'description',
        'mime_type',
        'size',
        'user_id',
        'collection_name', // e.g., 'summernote-content', 'page-featured', 'profile-avatar'
        'metadata',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'is_public' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the user that uploaded the image.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to get images used in Summernote content
     */
    public function scopeForSummernote($query)
    {
        return $query->where('collection_name', 'summernote-content');
    }

    /**
     * Scope to get page-related images
     */
    public function scopeForPages($query)
    {
        return $query->whereIn('collection_name', ['page-featured', 'page-content', 'page-gallery']);
    }
}