<?php

namespace Simorgh\Logger\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class LogEntry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'advanced_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'level',
        'category',
        'message',
        'context',
        'user_id',
        'ip_address',
        'user_agent',
        'request_id',
        'session_id',
        'route_name',
        'method',
        'url',
        'status_code',
        'execution_time',
        'memory_usage',
        'file',
        'line',
        'exception_class',
        'exception_message',
        'stack_trace',
        'tags',
        'extra',
        'is_resolved',
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'context' => 'array',
        'tags' => 'array',
        'extra' => 'array',
        'execution_time' => 'decimal:3',
        'memory_usage' => 'integer',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'stack_trace', // Hide stack traces by default for security
    ];

    /**
     * Get the user that owns the log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'), 'user_id');
    }

    /**
     * Scope a query to only include logs of a specific level.
     */
    public function scopeLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to only include logs of specific levels.
     */
    public function scopeLevels(Builder $query, array $levels): Builder
    {
        return $query->whereIn('level', $levels);
    }

    /**
     * Scope a query to only include logs of a specific category.
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include logs of specific categories.
     */
    public function scopeCategories(Builder $query, array $categories): Builder
    {
        return $query->whereIn('category', $categories);
    }

    /**
     * Scope a query to only include logs for a specific user.
     */
    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include logs within a date range.
     */
    public function scopeDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope a query to only include logs from today.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope a query to only include logs from yesterday.
     */
    public function scopeYesterday(Builder $query): Builder
    {
        return $query->whereDate('created_at', yesterday());
    }

    /**
     * Scope a query to only include logs from this week.
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope a query to only include logs from this month.
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Scope a query to only include unresolved logs.
     */
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope a query to only include resolved logs.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope a query to search in message and context.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('message', 'like', "%{$search}%")
              ->orWhere('context', 'like', "%{$search}%")
              ->orWhere('exception_message', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by tags.
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->whereJsonContains('tags', $tags);
    }

    /**
     * Scope a query to filter by request ID.
     */
    public function scopeRequestId(Builder $query, string $requestId): Builder
    {
        return $query->where('request_id', $requestId);
    }

    /**
     * Scope a query to filter by session ID.
     */
    public function scopeSessionId(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Apply filters to the query.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['level'])) {
            if (is_array($filters['level'])) {
                $query->levels($filters['level']);
            } else {
                $query->level($filters['level']);
            }
        }

        if (isset($filters['category'])) {
            if (is_array($filters['category'])) {
                $query->categories($filters['category']);
            } else {
                $query->category($filters['category']);
            }
        }

        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['tags'])) {
            $query->withTags($filters['tags']);
        }

        if (isset($filters['request_id'])) {
            $query->requestId($filters['request_id']);
        }

        if (isset($filters['session_id'])) {
            $query->sessionId($filters['session_id']);
        }

        if (isset($filters['is_resolved'])) {
            $query->where('is_resolved', $filters['is_resolved']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateRange(
                Carbon::parse($filters['date_from']),
                Carbon::parse($filters['date_to'])
            );
        } elseif (isset($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from']));
        } elseif (isset($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to']));
        }

        if (isset($filters['period'])) {
            match ($filters['period']) {
                'today' => $query->today(),
                'yesterday' => $query->yesterday(),
                'this_week' => $query->thisWeek(),
                'this_month' => $query->thisMonth(),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Get the formatted execution time.
     */
    public function getFormattedExecutionTimeAttribute(): string
    {
        if (!$this->execution_time) {
            return 'N/A';
        }

        if ($this->execution_time < 1) {
            return round($this->execution_time * 1000) . 'ms';
        }

        return round($this->execution_time, 3) . 's';
    }

    /**
     * Get the formatted memory usage.
     */
    public function getFormattedMemoryUsageAttribute(): string
    {
        if (!$this->memory_usage) {
            return 'N/A';
        }

        $bytes = $this->memory_usage;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the level badge color for UI.
     */
    public function getLevelColorAttribute(): string
    {
        return match ($this->level) {
            'emergency' => 'red',
            'alert' => 'red',
            'critical' => 'red',
            'error' => 'orange',
            'warning' => 'yellow',
            'notice' => 'blue',
            'info' => 'green',
            'debug' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the level badge icon for UI.
     */
    public function getLevelIconAttribute(): string
    {
        return match ($this->level) {
            'emergency' => '🚨',
            'alert' => '⚠️',
            'critical' => '🔥',
            'error' => '❌',
            'warning' => '⚠️',
            'notice' => 'ℹ️',
            'info' => '✅',
            'debug' => '🐛',
            default => '📝',
        };
    }

    /**
     * Mark the log entry as resolved.
     */
    public function markAsResolved(): bool
    {
        return $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark the log entry as unresolved.
     */
    public function markAsUnresolved(): bool
    {
        return $this->update([
            'is_resolved' => false,
            'resolved_at' => null,
        ]);
    }

    /**
     * Get logs statistics.
     */
    public static function getStats(array $filters = []): array
    {
        $query = static::query()->filter($filters);

        return [
            'total' => $query->count(),
            'by_level' => $query->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray(),
            'by_category' => $query->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'resolved' => $query->resolved()->count(),
            'unresolved' => $query->unresolved()->count(),
            'today' => $query->today()->count(),
            'this_week' => $query->thisWeek()->count(),
            'this_month' => $query->thisMonth()->count(),
        ];
    }
}
