<?php

namespace App\Rules;

use App\Models\Project;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueProjectAssignees implements ValidationRule
{
    protected $project;

    // constructor to get the project ID
    public function __construct($project)
    {
        $this->project = $project;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if the user is already assigned to the project
        if ($this->project->users()->where('user_id', $value)->exists()) {
            $fail("The user with ID {$value} is already assigned to this project.");
        }
    }
}
