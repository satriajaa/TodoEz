<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'deadline',
        'category_id',
        'priority',
        'recurring_pattern',
        'recurring_until',
        'reminder_at',
        'user_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'deadline' => 'date',
        'reminder_at' => 'datetime',
        'recurring_until' => 'date'
    ];

    /**
     * Get the category that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
{
    return $query->where('user_id', $userId);
}
}
