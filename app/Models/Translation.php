<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'locale',
        'content',
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Scope a query to filter by key.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereKey($query, $key)
    {
        return $query->where('key', 'like', "%{$key}%");
    }

    /**
     * Scope a query to filter by locale.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $locale
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereLocale($query, $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope a query to filter by content.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $content
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereContent($query, $content)
    {
        return $query->where('content', 'like', "%{$content}%");
    }

    /**
     * Scope a query to filter by tag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $tags
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereTags($query, $tags)
    {
        $tags = is_array($tags) ? $tags : [$tags];
        
        return $query->where(function ($query) use ($tags) {
            foreach ($tags as $tag) {
                $query->orWhereJsonContains('tags', $tag);
            }
        });
    }
} 