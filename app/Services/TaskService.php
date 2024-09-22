<?php

namespace App\Services;

use App\Models\Project;
use Exception;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use CodingPartners\AutoController\Traits\FileStorageTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService
{
    use ApiResponseTrait, FileStorageTrait;

    /**
     * list all Tasks information
     */
    public function listTask(int $perPage)
    {
        try {
            return Task::paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error Listing Task ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Create a new Task.
     * @param array $fieldInputs
     * @return \App\Models\Task
     */
    public function createTask(array $fieldInputs)
    {
        try {
            DB::beginTransaction();

            $Task = Task::create([
                'project_id'  => $fieldInputs["project_id"],
                'created_by'  => Auth::user()->id,
                'assigned_to' => $fieldInputs["assigned_to"],
                'title'       => $fieldInputs["title"],
                'description' => $fieldInputs["description"],
                'priority'    => $fieldInputs["priority"],
                'due_date'    => $fieldInputs["due_date"],
            ]);

            $Project = Project::findOrFail($fieldInputs["project_id"]);
            $Project->users()->updateExistingPivot(Auth::user()->id, ['last_activity' => now()]);

            DB::commit();

            return $Task;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating Task: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }


    /**
     * Get the details of a specific Task.
     *
     * @param \App\Models\Task $Task
     * @return \App\Models\Task
     */
    public function getTask(Task $Task)
    {
        try {
            return $Task;
        } catch (Exception $e) {
            Log::error('Error retrieving Task: ' . $e->getMessage());
            throw new Exception('Error retrieving Task.');
        }
    }

    /**
     * Update a specific Task.
     *
     * @param array $fieldInputs
     * @param Task $Task
     * @return \App\Models\Task
     */
    public function updateTask(array $fieldInputs, $Task)
    {
        try {
            DB::beginTransaction();

            $Task->update(array_filter($fieldInputs));

            // update user last activity
            $Project = Project::findOrFail($Task->project_id);
            $Project->users()->updateExistingPivot(Auth::user()->id, ['last_activity' => now()]);

            DB::commit();

            return $Task;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating Task: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Delete a specific Task.
     *
     * @param Task $Task
     * @return void
     */
    public function deleteTask($Task)
    {
        try {
            $Task->delete();
        } catch (Exception $e) {
            Log::error('Error deleting Task ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashedListTask($perPage)
    {
        try {
            return Task::onlyTrashed()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error Trashing Task ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Restore a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be restored.
     * @return \App\Models\Task
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the restore process.
     */
    public function restoreTask($id)
    {
        try {
            $Task = Task::onlyTrashed()->findOrFail($id);
            $Task->restore();
            return $Task;
        } catch (ModelNotFoundException $e) {
            Log::error('Task not found: ' . $e->getMessage());
            throw new Exception('Task not found.');
        } catch (Exception $e) {
            Log::error('Error restoring Task: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be permanently deleted.
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the force delete process.
     */
    public function forceDeleteTask($id)
    {
        try {
            $Task = Task::onlyTrashed()->findOrFail($id);

            $Task->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error('Task not found: ' . $e->getMessage());
            throw new Exception('Task not found.');
        } catch (Exception $e) {
            Log::error('Error force deleting Task ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Change the status of a task and update the user's last activity.
     *
     * This method updates the specified task with the provided data. It also updates
     * the authenticated user's last activity in the pivot table of the related project.
     * If an error occurs during the process, it logs the error and rolls back the transaction.
     *
     * @param array $fieldInputs The validated input data for updating the task.
     * @param Task $Task The task whose status will be changed.
     * @return Task The updated task instance.
     * @throws HttpResponseException If an error occurs during the update process.
     */
    public function changeStatusTask(array $fieldInputs, Task $Task)
    {
        try {
            DB::beginTransaction();

            $Task->update($fieldInputs);

            // Update user last activity
            $Project = Project::findOrFail($fieldInputs["project_id"]);
            $Project->users()->updateExistingPivot(Auth::user()->id, ['last_activity' => now()]);

            DB::commit();

            return $Task;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error changing status Task: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Add or update notes for a task and update the user's last activity.
     *
     * This method updates the specified task with the provided notes. It also updates
     * the authenticated user's last activity in the pivot table of the related project.
     * If an error occurs during the process, it logs the error and rolls back the transaction.
     *
     * @param array $fieldInputs The validated input data for updating the task notes.
     * @param Task $Task The task to which notes will be added or updated.
     * @return Task The updated task instance.
     * @throws HttpResponseException If an error occurs during the update process.
     */
    public function addNotesTask(array $fieldInputs, Task $Task)
    {
        try {
            DB::beginTransaction();

            $Task->update($fieldInputs);

            // Update user last activity
            $Project = Project::findOrFail($fieldInputs["project_id"]);
            $Project->users()->updateExistingPivot(Auth::user()->id, ['last_activity' => now()]);

            DB::commit();

            return $Task;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error adding notes Task: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Retrieve all tasks related to the projects the authenticated user is involved in.
     *
     * This method attempts to load all tasks for the authenticated user by retrieving
     * the user instance and loading their related tasks. If an error occurs during the
     * process, it logs the error and throws an HTTP response exception.
     *
     * @return User The user instance with their tasks loaded.
     * @throws HttpResponseException If an error occurs during task retrieval.
     */
    public function getAllTasksInMyProjects()
    {
        try {
            $user = User::findOrFail(auth()->user()->id);
            return $user->load('tasks');
        } catch (Exception $e) {
            Log::error('Error retrieving user tasks: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }
}
