<?php

namespace App\Http\Requests\ProjectRequest;

use App\Rules\UniqueManager;
use App\Rules\UniqueProjectAssignees;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class AssignProjectMembersRequest extends FormRequest
{
    use ApiResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('Project'); // get project id from the route

        return [
            'users'        => ["required", "array", new UniqueManager($project)],
            'users.*.id'   => ['required', 'exists:users,id', new UniqueProjectAssignees($project)],
            'users.*.role' => 'required|string|in:manager,developer,tester',
        ];
    }

    /**
     *  method handles failure of Validation and return message
     * @param \Illuminate\Contracts\Validation\Validator $Validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return never
     */
    protected function failedValidation(Validator $Validator)
    {
        $errors = $Validator->errors()->all();
        throw new HttpResponseException($this->errorResponse($errors, 'Validation error', 422));
    }
}
