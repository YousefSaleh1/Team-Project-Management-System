<?php

namespace App\Http\Middleware;

use App\Models\Task;
use App\Models\User;
use Closure;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProjectRole
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user_id = auth()->user()->id; // Get the authenticated user's ID
            $user = User::findOrFail($user_id); // Find the user

            if ($user->is_admin) {
                return $next($request);
            }

            // Retrieve the task ID from the route
            if ($task = $request->route('Task')) {
                // If the task is not found, return a 404 error response
                if (!$task) {
                    throw new HttpResponseException($this->errorResponse(null, 'not found', 404));
                }

                // Get the project ID associated with the task
                $projectId = $task->project_id;
            }else {
                $projectId = $request->input('project_id');
            }

            // Check if the user has the required role for the project
            try {
                $userRole = $user->getRoleForProject($projectId);
            } catch (\Exception $e) {
                // If an exception occurs, return an unauthorized error response
                throw new HttpResponseException($this->errorResponse(null, 'Unauthorized', 401));
            }

            if (in_array($userRole, $roles)) {
                return $next($request);
            }

        }

        // If the user is not authenticated, return an unauthorized error response
        throw new HttpResponseException($this->errorResponse(null, 'Unauthorized', 401));
    }
}
