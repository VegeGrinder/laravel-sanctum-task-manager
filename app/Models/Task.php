<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = [
        'days_left',
        'is_completed',
        'is_archived',
    ];
    protected $casts = [
        'tags' => 'array'
    ];

    public function getDaysLeftAttribute(): int
    {
        $date = Carbon::parse($this->due_date);
        $now = Carbon::now()->startOfDay();

        return $date->diffInDays($now);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_date != null ? 1 : 0;
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->archived_date != null ? 1 : 0;
    }

    public function scopeFilter(Builder $query): void
    {
        $title = request('title', null);
        $description = request('description', null);
        $priorityLevel = request('priority_level', null);
        $dueDateFrom = request('due_date_from', null);
        $dueDateTo = request('due_date_to', null);
        $completedDateFrom = request('completed_date_from', null);
        $completedDateTo = request('completed_date_to', null);
        $archivedDateFrom = request('archived_date_from', null);
        $archivedDateTo = request('archived_date_to', null);

        if (!empty($title))
            $query->where('title', 'LIKE', "%$title%");

        if (!empty($description))
            $query->where('description', 'LIKE', "%$description%");

        if (!empty($priorityLevel))
            $query->where('priority_level', $priorityLevel);

        if (!empty($dueDateFrom))
            $query->where('due_date', '>=', $dueDateFrom);

        if (!empty($dueDateTo))
            $query->where('due_date', '<=', $dueDateTo);

        if (!empty($completedDateFrom))
            $query->where('completed_date', '>=', $completedDateFrom);

        if (!empty($completedDateTo))
            $query->where('completed_date', '<=', $completedDateTo);

        if (!empty($archivedDateFrom))
            $query->where('archived_date', '>=', $archivedDateFrom);

        if (!empty($archivedDateTo))
            $query->where('archived_date', '<=', $archivedDateTo);
    }
}
