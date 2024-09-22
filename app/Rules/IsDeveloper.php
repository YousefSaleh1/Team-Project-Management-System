<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class IsDeveloper implements ValidationRule
{
    use ApiResponseTrait;

    protected $action;

    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user_id = request()->input('assigned_to');
        $user = User::findOrFail($user_id);

        // Get the project ID from the request or the route
        switch ($this->action) {
            case 'store':
                $projectId = request()->input('project_id');
                break;

            case 'update':
                $Task = request()->route('Task');
                $projectId = $Task->project_id;
                dd($projectId);
                break;

            default:
                Log::error('this is not a valid project in IsDeveloper ValidationRule');
                throw new HttpResponseException($this->errorResponse(null, 'something error', 500));
                break;
        }

        if ($user->getRoleForProject($projectId) != 'developer') {
            $fail('You can only assign the task to a developer!');
        }
    }
}
