<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get all of the tasks for the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id', 'id');
    }

    /**
     * The users that belong to the Project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role', 'contribution_hours', 'last_activity')
            ->withTimestamps();
    }

    /**
     * Get the latest task related to the project based on a created_at column.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestTask()
    {
        return $this->hasOne(Task::class)->latestOfMany();
    }

    /**
     * Get the oldest task related to the project based on a created_at column.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestTask()
    {
        return $this->hasOne(Task::class)->oldestOfMany();
    }

    /**
     * Get the highest priority task based on a specific condition in the title.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function highestPriorityTask()
    {
        return $this->hasOne(Task::class)
            ->where('priority', 'high')
            ->ofMany('created_at', 'max');
    }
}
