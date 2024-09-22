<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'projects'      => ProjectResource::collection($this->whenLoaded('projects')),
            'tasks'         => TaskResource::collection($this->whenLoaded('tasks')),
            'createdTasks'  => TaskResource::collection($this->whenLoaded('createdTasks')),
            'assignedTasks' => TaskResource::collection($this->whenLoaded('assignedTasks')),
        ];
    }
}
