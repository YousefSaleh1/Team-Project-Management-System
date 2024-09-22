<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use CodingPartners\AutoController\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, ApiResponseTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $guarded = ['is_admin'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get all tasks created by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by', 'id');
    }

    /**
     * Get all tasks assigned to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to', 'id');
    }

    /**
     * The projects that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role', 'contribution_hours', 'last_activity')
            ->withTimestamps();
    }

    /**
     * Get all of the tasks for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(
            Task::class,
            ProjectUser::class, // Use the pivot table
            'user_id', // The foreign key in the project_user table that points to the user
            'project_id', // The foreign key in the tasks table that points to the project
            'id', // The local key in the users table
            'project_id' // The foreign key in the project_user table that points to the project
        );
    }

    /**
     * Get the role of the user for a specific project.
     *
     * This method retrieves the role of the user in a specific project
     * using the pivot table (project_user) in a many-to-many relationship.
     * Throws an exception if the user is not part of the project.
     *
     * @param int $projectId The ID of the project.
     * @return string The role of the user in the project.
     * @throws \HttpResponseException If the user is not part of the project.
     */
    public function getRoleForProject($projectId)
    {
        // Fetch the project associated with the user by its ID
        $project = $this->projects()->where('project_id', $projectId)->first();

        // If the user is not associated with the project, throw an exception
        if (!$project) {
            throw new HttpResponseException($this->errorResponse(null, 'User is not part of this project.', 404));
        }

        // Return the role from the pivot table
        return $project->pivot->role;
    }

    public function scopeFilterTasks($query, $status = null, $priority = null)
    {
        return $query->whereRelation('assignedTasks', function ($taskQuery) use ($status, $priority) {
            if (!empty($status)) {
                $taskQuery->where('status', $status);
            }
            if (!empty($priority)) {
                $taskQuery->where('priority', $priority);
            }
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
