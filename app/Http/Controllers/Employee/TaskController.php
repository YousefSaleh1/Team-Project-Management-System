<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest\AddNotesRequest;
use App\Http\Requests\TaskRequest\ChangeStatusTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Http\Resources\TaskResource;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use App\Http\Requests\TaskRequest\StoreTaskRequest;
use App\Http\Requests\TaskRequest\UpdateTaskRequest;
use App\Http\Resources\UserResource;

class TaskController extends Controller
{
    use ApiResponseTrait;

    /**
     * @var TaskService
     */
    protected $TaskService;

    /**
     *  TaskController constructor
     * @param TaskService $TaskService
     */
    public function __construct(TaskService $TaskService)
    {
        $this->TaskService = $TaskService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 if not provided
        $Tasks = $this->TaskService->listTask($perPage);
        return $this->resourcePaginated(TaskResource::collection($Tasks));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $fieldInputs = $request->validated();
        $Task        = $this->TaskService->createTask($fieldInputs);
        return $this->successResponse(new TaskResource($Task), "Task Created Successfully", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $Task)
    {
        return $this->successResponse(new TaskResource($Task));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $Task)
    {
        $fieldInputs = $request->validated();
        $Task    = $this->TaskService->updateTask($fieldInputs, $Task);
        return $this->successResponse(new TaskResource($Task), "Task Updated Successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $Task)
    {
        $this->TaskService->deleteTask($Task);
        return $this->successResponse(null, "Task Deleted Successfully");
    }

    /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashedTasks = $this->TaskService->trashedListTask($perPage);
        return $this->resourcePaginated(TaskResource::collection($trashedTasks));
    }

    /**
     * Restore a trashed (soft deleted) resource by its ID.
     */
    public function restore($id)
    {
        $Task = $this->TaskService->restoreTask($id);
        return $this->successResponse(new TaskResource($Task), "Task restored Successfully");
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     */
    public function forceDelete($id)
    {
        $this->TaskService->forceDeleteTask($id);
        return $this->successResponse(null, "Task deleted Permanently");
    }

    /**
     * Change the status of a specific task.
     *
     * This method validates the incoming request data using
     * the ChangeStatusTaskRequest. It then calls the TaskService to
     * update the status of the specified task. A successful response
     * is returned with the updated task resource.
     *
     * @param ChangeStatusTaskRequest $request The validated request containing the new status for the task.
     * @param Task $Task The task whose status will be updated.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success with the updated task resource.
     */
    public function changeStatus(ChangeStatusTaskRequest $request, Task $Task)
    {
        $fieldInputs = $request->validated();
        $Task        = $this->TaskService->changeStatusTask($fieldInputs, $Task);
        return $this->successResponse(new TaskResource($Task), "Task status updated Successfully", 200);
    }


    /**
     * Add notes to a specific task.
     *
     * This method validates the incoming request data using
     * the AddNotesRequest. It then calls the TaskService to
     * add or update the notes for the specified task. A successful
     * response is returned with the updated task resource.
     *
     * @param AddNotesRequest $request The validated request containing the new notes for the task.
     * @param Task $Task The task to which notes will be added.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success with the updated task resource.
     */
    public function addNotes(AddNotesRequest $request, Task $Task)
    {
        $fieldInputs = $request->validated();
        $Task        = $this->TaskService->addNotesTask($fieldInputs, $Task);
        return $this->successResponse(new TaskResource($Task), "Task notes updated Successfully", 200);
    }

    /**
     * Retrieve all tasks in the projects that the authenticated user is involved in.
     *
     * This method fetches all tasks related to the projects where the authenticated user
     * is a member. It calls the TaskService to perform the retrieval and returns a successful
     * response with the tasks data.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the tasks in the user's projects.
     */
    public function getAllTasksInMyProjects()
    {
        $tasks = $this->TaskService->getAllTasksInMyProjects();
        return $this->successResponse(new UserResource($tasks));
    }
}
