<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterAnalytic extends Model
{
    protected $fillable = [
        'filter_type',
        'filter_value',
        'filter_slug',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user who used the filter
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get most popular filters by type
     */
    public static function getPopularFilters($type = null, $limit = 10, $days = 30)
    {
        $query = static::where('created_at', '>=', now()->subDays($days));

        if ($type) {
            $query->where('filter_type', $type);
        }

        return $query->selectRaw('filter_type, filter_value, filter_slug, COUNT(*) as usage_count')
            ->groupBy('filter_type', 'filter_value', 'filter_slug')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get filter usage statistics
     */
    public static function getFilterStats($days = 30)
    {
        return static::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('filter_type, COUNT(*) as total_uses, COUNT(DISTINCT filter_value) as unique_values')
            ->groupBy('filter_type')
            ->orderByDesc('total_uses')
            ->get();
    }
}
