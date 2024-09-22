<?php

namespace App\Services;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use CodingPartners\AutoController\Traits\FileStorageTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserService
{
    use ApiResponseTrait, FileStorageTrait;

    /**
     * list all Users information
     */
    public function listUser(int $perPage, $status, $priority)
    {
        try {
            return User::with('assignedTasks')->filterTasks($status, $priority)->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error Listing User ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Create a new User.
     * @param array $fieldInputs
     * @return \App\Models\User
     */
    public function createUser(array $fieldInputs)
    {
        try {
            return User::create([
                'name' => $fieldInputs["name"],
                'email' => $fieldInputs["email"],
                'password' => $fieldInputs["password"],
            ]);
        } catch (Exception $e) {
            Log::error('Error creating User: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }


    /**
     * Get the details of a specific User.
     *
     * @param \App\Models\User $User
     * @return \App\Models\User
     */
    public function getUser(User $User)
    {
        try {
            return $User;
        } catch (Exception $e) {
            Log::error('Error retrieving User: ' . $e->getMessage());
            throw new Exception('Error retrieving User.');
        }
    }

    /**
     * Update a specific User.
     *
     * @param array $fieldInputs
     * @param User $User
     * @return \App\Models\User
     */
    public function updateUser(array $fieldInputs, $User)
    {
        try {
            $User->update(array_filter($fieldInputs));
            return $User;
        } catch (Exception $e) {
            Log::error('Error updating User: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Delete a specific User.
     *
     * @param User $User
     * @return void
     */
    public function deleteUser($User)
    {
        try {
            $User->delete();
        } catch (Exception $e) {
            Log::error('Error deleting User ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashedListUser($perPage)
    {
        try {
            return User::onlyTrashed()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error Trashing User ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Restore a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed User to be restored.
     * @return \App\Models\User
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the User with the given ID is not found.
     * @throws \Exception If there is an error during the restore process.
     */
    public function restoreUser($id)
    {
        try {
            $User = User::onlyTrashed()->findOrFail($id);
            $User->restore();
            return $User;
        } catch (ModelNotFoundException $e) {
            Log::error('User not found: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'User not found', 404));
        } catch (Exception $e) {
            Log::error('Error restoring User: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed User to be permanently deleted.
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the User with the given ID is not found.
     * @throws \Exception If there is an error during the force delete process.
     */
    public function forceDeleteUser($id)
    {
        try {
            $User = User::onlyTrashed()->findOrFail($id);

            $User->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error('User not found: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'User not found', 404));
        } catch (Exception $e) {
            Log::error('Error force deleting User ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }
}
