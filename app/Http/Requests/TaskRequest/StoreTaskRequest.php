<?php

namespace App\Http\Requests\TaskRequest;

use App\Rules\IsDeveloper;
use App\Rules\UserExistsInProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use CodingPartners\AutoController\Traits\ApiResponseTrait;

class StoreTaskRequest extends FormRequest
{
    use ApiResponseTrait;

    // stop validation in the first failure
    protected $stopOnFirstFailure = false;


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'project_id'  => 'required|exists:projects,id',
            'assigned_to' => ['required', 'exists:users,id', new UserExistsInProject('store'), new IsDeveloper('store')],
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|string|in:low,medium,high',
            'due_date'    => 'required|date',
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
