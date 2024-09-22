<?php

namespace App\Services;

use App\Models\User;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    use ApiResponseTrait;

    public function login(array $credentials)
    {

        $token  = Auth::attempt($credentials);

        if (!$token) {
            throw new HttpResponseException($this->errorResponse(null, 'Unauthorized', 401));
        }

        $user = Auth::user();
        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
