<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Http\Requests\StoreTasksRequest;
use App\Http\Requests\UpdateTasksRequest;
use App\Repositories\TaskRepository;
use App\Policies\TaskPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Resources\TaskCollection;

class TaskController extends Controller
{
    protected $taskRepository;


    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function index()
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskRepository->all();
        return new TaskCollection($tasks);
    }

    public function store(StoreTasksRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $data['status'] = 'To Do';

        $task = $this->taskRepository->create($data);
        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    public function update(UpdateTasksRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validated();
        if (isset($data['status'])) {

            $task->status = $data['status'];
        }

        $updatedTask = $this->taskRepository->update($task, $data);
        return response()->json($updatedTask);
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $this->taskRepository->delete($task);
        return response()->json(['message' => 'Task deleted']);
    }
}
