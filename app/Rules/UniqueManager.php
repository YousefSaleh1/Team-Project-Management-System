<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueManager implements ValidationRule
{
    protected $project;

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
        // Count the number of users with the 'manager' role from the existing project members
        $existingManagersCount = $this->project->users()->where('role', 'manager')->count();

        // Count the number of managers
        $newManagerCount = collect($value)->where('role', 'manager')->count();

        // If there is more than one manager, fail the validation
        if ($newManagerCount + $existingManagersCount > 1) {
            $fail('There can be only one manager for the project.');
        }
    }
}
