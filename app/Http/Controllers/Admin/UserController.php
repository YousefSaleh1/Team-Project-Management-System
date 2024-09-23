<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Resources\UserResource;
use CodingPartners\AutoController\Traits\ApiResponseTrait;
use App\Http\Requests\UserRequest\StoreUserRequest;
use App\Http\Requests\UserRequest\UpdateUserRequest;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use ApiResponseTrait;

    /**
     * @var UserService
     */
    protected $UserService;

    /**
     *  UserController constructor
     * @param UserService $UserService
     */
    public function __construct(UserService $UserService)
    {
        $this->UserService = $UserService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 if not provided
        $Users = $this->UserService->listUser($perPage);
        return $this->resourcePaginated(UserResource::collection($Users));
    }

    public function getUsersWithAssignedTasks(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 if not provided
        $Users = $this->UserService->getUsersWithAssignedTasks($perPage, $request->input('status'), $request->input('priority'));
        return $this->resourcePaginated(UserResource::collection($Users));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $fieldInputs = $request->validated();
        $User        = $this->UserService->createUser($fieldInputs);
        return $this->successResponse(new UserResource($User), "User Created Successfully", 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $User)
    {
        return $this->successResponse(new UserResource($User));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $User)
    {
        $fieldInputs = $request->validated();
        $User        = $this->UserService->updateUser($fieldInputs, $User);
        return $this->successResponse(new UserResource($User), "User Updated Successfully", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $User)
    {
        $this->UserService->deleteUser($User);
        return $this->successResponse(null, "User Deleted Successfully");
    }



    /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashed(Request $request)
    {
        $perPage      = $request->input('per_page', 10);
        $trashedUsers = $this->UserService->trashedListUser($perPage);
        return $this->resourcePaginated(UserResource::collection($trashedUsers));
    }

    /**
     * Restore a trashed (soft deleted) resource by its ID.
     */
    public function restore($id)
    {
        $User = $this->UserService->restoreUser($id);
        return $this->successResponse(new UserResource($User), "User restored Successfully");
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     */
    public function forceDelete($id)
    {
        $this->UserService->forceDeleteUser($id);
        return $this->successResponse(null, "User deleted Permanently");
    }
}
