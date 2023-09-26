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
    protected $appends = ['days_left'];
    protected $casts = [
        'tags' => 'array'
    ];

    // public function getPriorityLevelAttribute($value)
    // {
    //     return $value != null ? PriorityLevel::getDescription($value) : null;
    // }

    public function getDaysLeftAttribute($value)
    {
        $date = Carbon::parse($this->due_date);
        $now = Carbon::now()->startOfDay();

        return $date->diffInDays($now);
    }
}
