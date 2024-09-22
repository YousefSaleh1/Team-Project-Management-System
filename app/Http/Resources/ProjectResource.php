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
            'users'               => UserResource::collection($this->whenLoaded('users')),
            'tasks'               => TaskResource::collection($this->whenLoaded('tasks')),
            'latestTask'          => new TaskResource($this->whenLoaded('latestTask')),
            'oldestTask'          => new TaskResource($this->whenLoaded('oldestTask')),
            'highestPriorityTask' => new TaskResource($this->whenLoaded('highestPriorityTask'))
        ];
    }
}
