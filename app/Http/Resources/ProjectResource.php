<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'description'         => $this->description,
            'role'                => $this->whenPivotLoaded('project_user', function () {
                return $this->pivot->role;
            }),
            'contribution_hours'  => $this->whenPivotLoaded('project_user', function () {
                return $this->pivot->contribution_hours;
            }),
            'last_activity'       => $this->whenPivotLoaded('project_user', function () {
                return $this->pivot->last_activity;
            }),
            'users'               => UserResource::collection($this->whenLoaded('users')),
            'tasks'               => TaskResource::collection($this->whenLoaded('tasks')),
            'latestTask'          => new TaskResource($this->whenLoaded('latestTask')),
            'oldestTask'          => new TaskResource($this->whenLoaded('oldestTask')),
            'highestPriorityTask' => new TaskResource($this->whenLoaded('highestPriorityTask'))
        ];
    }
}
