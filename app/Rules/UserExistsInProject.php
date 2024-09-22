<?php

namespace App\Rules;

use App\Models\Project;
use Closure;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;


class UserExistsInProject implements ValidationRule
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
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

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
                Log::error('this is not a valid project in UserExistsInProject ValidationRule');
                throw new HttpResponseException($this->errorResponse(null, 'something error', 500));
                break;
        }

        // Check if the user is part of the project
        if (!Project::find($projectId)->users()->where('users.id', $value)->exists()) {
            $fail('The selected user is not associated with the project.');
        }
    }
}
