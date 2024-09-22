<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest\AddContributionHoursRequest;
use App\Http\Requests\ProjectRequest\AssignProjectMembersRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\ProjectService;
use App\Http\Resources\ProjectResource;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use App\Http\Requests\ProjectRequest\StoreProjectRequest;
use App\Http\Requests\ProjectRequest\UnAssignProjectMembersRequest;
use App\Http\Requests\ProjectRequest\UpdateProjectRequest;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    /**
     * @var ProjectService
     */
    protected $ProjectService;

    /**
     *  ProjectController constructor
     * @param ProjectService $ProjectService
     */
    public function __construct(ProjectService $ProjectService)
    {
        $this->ProjectService = $ProjectService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage     = $request->input('per_page', 10); // Default to 10 if not provided
        $latest_task = $request->input('latest_task', false);
        $oldest_task = $request->input('oldest_task', false);

        $Projects = $this->ProjectService->listProject($perPage, $latest_task, $oldest_task);
        return $this->resourcePaginated(ProjectResource::collection($Projects));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $fieldInputs = $request->validated();
        $Project     = $this->ProjectService->createProject($fieldInputs);
        return $this->successResponse(new ProjectResource($Project), "Project Created Successfully", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $Project)
    {
        $title   = $request->input('title');
        $Project = $this->ProjectService->getProject($Project, $title);

        return $this->successResponse(new ProjectResource($Project));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $Project)
    {
        $fieldInputs = $request->validated();
        $Project     = $this->ProjectService->updateProject($fieldInputs, $Project);
        return $this->successResponse(new ProjectResource($Project), "Project Updated Successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $Project)
    {
        $this->ProjectService->deleteProject($Project);
        return $this->successResponse(null, "Project Deleted Successfully");
    }



    /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashed(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashedProjects = $this->ProjectService->trashedListProject($perPage);
        return $this->resourcePaginated(ProjectResource::collection($trashedProjects));
    }

    /**
     * Restore a trashed (soft deleted) resource by its ID.
     */
    public function restore($id)
    {
        $Project = $this->ProjectService->restoreProject($id);
        return $this->successResponse(new ProjectResource($Project), "Project restored Successfully");
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     */
    public function forceDelete($id)
    {
        $this->ProjectService->forceDeleteProject($id);
        return $this->successResponse(null, "Project deleted Permanently");
    }

    /**
     * Assign users to a specific project.
     *
     * @param  \App\Http\Requests\AssignProjectMembersRequest  $request  The request containing user IDs and roles to be assigned to the project.
     * @param  \App\Models\Project  $Project  The project instance to which users will be assigned.
     * @return \Illuminate\Http\JsonResponse  The response containing the updated project details and a success message.
     */
    public function assignProjectMembers(AssignProjectMembersRequest $request, Project $Project)
    {
        $fieldInputs = $request->validated();
        $Project = $this->ProjectService->assignProjectMembers($fieldInputs, $Project);

        return $this->successResponse(new ProjectResource($Project), "Users assigned to the project successfully");
    }

    /**
     * Unassign users from a specific project.
     *
     * @param  \App\Http\Requests\UnAssignProjectMembersRequest  $request  The request containing user IDs to be unassigned from the project.
     * @param  \App\Models\Project  $Project  The project instance from which users will be unassigned.
     * @return \Illuminate\Http\JsonResponse  The response containing the updated project details and a success message.
     */
    public function unassignProjectMembers(UnAssignProjectMembersRequest $request, Project $Project)
    {
        $fieldInputs = $request->validated();
        $Project = $this->ProjectService->unassignProjectMembers($fieldInputs, $Project);

        return $this->successResponse(new ProjectResource($Project), "Users unassigned from the project successfully");
    }

    /**
     * Add contribution hours to a project.
     *
     * This method validates the incoming request data using
     * the AddContributionHoursRequest. It then calls the
     * ProjectService to add the contribution hours to the
     * specified project. Finally, it returns a successful
     * response with the updated project data.
     *
     * @param AddContributionHoursRequest $request The validated request containing contribution hours.
     * @param Project $Project The project to which contribution hours will be added.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success with the updated project resource.
     */
    public function addContributionHours(AddContributionHoursRequest $request, Project $Project)
    {
        $fieldInputs = $request->validated();
        $Project     = $this->ProjectService->addContributionHoursProject($fieldInputs, $Project);

        return $this->successResponse(new ProjectResource($Project), "User Add Contribution Hours successfully");
    }

}
