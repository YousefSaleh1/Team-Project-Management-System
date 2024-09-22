<?php

namespace App\Services;

use Exception;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use CodingPartners\AutoController\Traits\FileStorageTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    use ApiResponseTrait, FileStorageTrait;

    /**
     * list all Projects information
     */
    public function listProject(int $perPage, $latest_task, $oldest_task)
    {
        try {
            $query     = Project::query();

            $relations = [];
            if ($latest_task) {
                array_push($relations, 'latestTask');
            }

            if ($oldest_task) {
                array_push($relations, 'oldestTask');
            }

            if ($relations) {
                $query->with($relations);
            }

            return $query->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error Listing Project ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Create a new Project.
     * @param array $fieldInputs
     * @return \App\Models\Project
     */
    public function createProject(array $fieldInputs)
    {
        try {
            return Project::create([
                'name' => $fieldInputs["name"],
                'description' => $fieldInputs["description"],
            ]);
        } catch (Exception $e) {
            Log::error('Error creating Project: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }


    /**
     * Get the details of a specific Project.
     *
     * @param \App\Models\Project $Project
     * @return \App\Models\Project
     */
    public function getProject(Project $Project, $title)
    {
        try {
            if ($title) {
                $Project->load(['highestPriorityTask' => function ($query) use ($title) {
                    $query->where('title', 'LIKE', '%'. $title . '%');
                }]);
            }else{
                $Project->load('tasks');
            }
            $Project->load('users');
            return $Project;
        } catch (Exception $e) {
            Log::error('Error retrieving Project: ' . $e->getMessage());
            throw new Exception('Error retrieving Project.');
        }
    }

    /**
     * Update a specific Project.
     *
     * @param array $fieldInputs
     * @param Project $Project
     * @return \App\Models\Project
     */
    public function updateProject(array $fieldInputs, Project $Project)
    {
        try {
            $Project->update(array_filter($fieldInputs));
            return $Project;
        } catch (Exception $e) {
            Log::error('Error updating Project:' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Delete a specific Project.
     *
     * @param Project $Project
     * @return void
     */
    public function deleteProject(Project $Project)
    {
        try {
            $Project->delete();
        } catch (Exception $e) {
            Log::error('Error deleting Project ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashedListProject($perPage)
    {
        try {
            return Project::onlyTrashed()->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error Trashing Project ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Restore a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Project to be restored.
     * @return \App\Models\Project
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Project with the given ID is not found.
     * @throws \Exception If there is an error during the restore process.
     */
    public function restoreProject($id)
    {
        try {
            $Project = Project::onlyTrashed()->findOrFail($id);
            $Project->restore();
            return $Project;
        } catch (ModelNotFoundException $e) {
            Log::error('Project not found: ' . $e->getMessage());
            throw new Exception('Project not found.');
        } catch (Exception $e) {
            Log::error('Error restoring Project: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Project to be permanently deleted.
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Project with the given ID is not found.
     * @throws \Exception If there is an error during the force delete process.
     */
    public function forceDeleteProject($id)
    {
        try {
            $Project = Project::onlyTrashed()->findOrFail($id);
            $Project->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error('Project not found: ' . $e->getMessage());
            throw new Exception('Project not found.');
        } catch (Exception $e) {
            Log::error('Error force deleting Project ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'there is something wrong in server', 500));
        }
    }

    /**
     * Assign users to a specific project with their respective roles.
     *
     * This method synchronizes the users associated with the given project,
     * updating their roles based on the provided input.
     *
     * @param  array  $fieldInputs  The validated input data containing user IDs and their roles.
     * @param  \App\Models\Project  $Project  The project instance to which users will be assigned.
     * @return \App\Models\Project  The updated project instance with the users loaded.
     * @throws \HttpResponseException  If there is an error during the operation.
     */
    public function assignProjectMembers(array $fieldInputs, Project $Project)
    {
        try {
            foreach ($fieldInputs['users'] as $user) {
                $Project->users()->attach($user['id'], ['role' => $user['role']]);
            }
            $Project->load('users');
            return $Project;
        } catch (Exception $e) {
            Log::error('Error assigning project members: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'There is something wrong on the server', 500));
        }
    }

    /**
     * Unassign users from a specific project.
     *
     * This method detaches the specified users from the given project.
     *
     * @param  array  $fieldInputs  The validated input data containing user IDs to be unassigned.
     * @param  \App\Models\Project  $Project  The project instance from which users will be unassigned.
     * @return \App\Models\Project  The updated project instance with the users loaded.
     * @throws \HttpResponseException  If there is an error during the operation.
     */
    public function unassignProjectMembers(array $fieldInputs, Project $Project)
    {
        try {
            foreach ($fieldInputs['users'] as $user) {
                $Project->users()->detach(array_column($fieldInputs['users'], 'id'));
            }
            $Project->load('users');
            return $Project;
        } catch (Exception $e) {
            Log::error('Error unassigning project members: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'There is something wrong on the server', 500));
        }
    }

    /**
     * Add contribution hours for a specific user to a project.
     *
     * This method updates the contribution hours of the authenticated user
     * in the pivot table for the specified project. It handles any exceptions
     * that may occur during the update process and logs an error message
     * if an exception is thrown.
     *
     * @param array $fieldInputs The input data containing the contribution hours.
     * @param Project $Project The project to which the contribution hours will be added.
     * @return Project The updated project instance with the latest user data loaded.
     * @throws HttpResponseException If an error occurs during the update process.
     */
    public function addContributionHoursProject(array $fieldInputs, Project $Project)
    {
        try {
            $Project->users()->updateExistingPivot(Auth::user()->id, ['contribution_hours' => $fieldInputs['contribution_hours']]);
            $Project->load('users');
            return $Project;
        } catch (\Exception $e) {
            Log::error('Error unassigning project members: ' . $e->getMessage());
            throw new HttpResponseException($this->errorResponse(null, 'There is something wrong on the server', 500));
        }
    }

}
