<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    protected $fillable = [
        'query',
        'results_count',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'results_count' => 'integer',
    ];

    /**
     * Get the user who performed the search
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get popular search queries
     */
    public static function getPopularQueries($limit = 10, $days = 30)
    {
        return static::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('query, COUNT(*) as search_count, AVG(results_count) as avg_results')
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get queries with zero results
     */
    public static function getZeroResultQueries($limit = 10, $days = 30)
    {
        return static::where('created_at', '>=', now()->subDays($days))
            ->where('results_count', 0)
            ->selectRaw('query, COUNT(*) as search_count')
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();
    }
}
