<?php

use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Employee\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware(['auth:api'])->group(function () {

    Route::middleware(['admin'])->group(function () {

        /**
         * User Management Routes for admin
         *
         * These routes handle User management operations.
         */
        Route::apiResource('Users', UserController::class);
        Route::get('Users/assigned-tasks', [UserController::class, 'getUsersWithAssignedTasks']);
        Route::get('Users/trashed', [UserController::class, 'trashed']);
        Route::post('Users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('Users/{id}/forceDelete', [UserController::class, 'forceDelete']);

        /**
         * Project Management Routes for admin
         *
         * These routes handle Project management operations.
         */
        Route::apiResource('Projects', ProjectController::class)->except('index', 'show');
        Route::get('Projects/trashed', [ProjectController::class, 'trashed']);
        Route::post('Projects/{id}/restore', [ProjectController::class, 'restore']);
        Route::delete('Projects/{id}/forceDelete', [ProjectController::class, 'forceDelete']);
        Route::post('assign-project-members/{Project}', [ProjectController::class, 'assignProjectMembers']);
        Route::post('unassign-project-members/{Project}', [ProjectController::class, 'unassignProjectMembers']);

        /**
         * Task Management Routes for admin
         *
         * These routes handle Task management operations.
         */
        Route::delete('Tasks-delete/{Task}', [TaskController::class, 'destroy']);
        Route::get('Tasks/trashed', [TaskController::class, 'trashed']);
        Route::post('Tasks/{id}/restore', [TaskController::class, 'restore']);
        Route::delete('Tasks/{id}/forceDelete', [TaskController::class, 'forceDelete']);
    });

    Route::middleware(['role:manager'])->group(function () {

        /**
         * Task Management Routes for admin and manager
         *
         * These routes handle Task management operations.
         */
        Route::apiResource('Tasks', TaskController::class)->only('store', 'update');

    });

    Route::middleware(['role:developer'])->group(function () {

        Route::put('change-status-task/{Task}', [TaskController::class, 'changeStatus']);

    });

    Route::middleware(['role:tester'])->group(function () {

        Route::put('add-notes-task/{Task}', [TaskController::class, 'addNotes']);

    });

    Route::middleware(['role:manager,developer,tester'])->group(function () {
        Route::put('add-contribution-hours/{Project}', [ProjectController::class, 'addContributionHours']);
    });

    Route::get('tasks-in-my-projects', [TaskController::class, 'getAllTasksInMyProjects']);

    Route::get('Projects', [ProjectController::class, 'index']);
    Route::get('Projects/{Project}', [ProjectController::class, 'show']);

    Route::get('Tasks', [TaskController::class, 'index']);
    Route::get('Tasks/{Task}', [TaskController::class, 'show']);
});
