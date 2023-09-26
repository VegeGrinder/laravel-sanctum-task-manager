<?php

namespace App\Models;

use App\Enums\PriorityLevel;
use Carbon\Carbon;
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

    public function getDaysLeftAttribute()
    {
        $date = Carbon::parse($this->due_date);
        $now = Carbon::now()->startOfDay();

        return $date->diffInDays($now);
    }

    public function getIsCompletedAttribute()
    {
        return $this->completed_date != null ? 1 : 0;
    }

    public function getIsArchivedAttribute()
    {
        return $this->archived_date != null ? 1 : 0;
    }
}
