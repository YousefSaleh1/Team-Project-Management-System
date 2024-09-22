<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AssignedUserIsNotAdmin implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Retrieve the assigned user by the provided ID
        $assignedUser = User::find($value);

        // Check if the user exists and is an admin
        if ($assignedUser && $assignedUser->is_admin) {
            // Call the fail closure with the error message
            $fail('The assigned user cannot be an admin.');
        }
    }
}
