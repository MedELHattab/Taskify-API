<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a new task in API version 2.
     *
     * @return void
     */
    public function testCreateNewTask()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/tasks/store', [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'To Do',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'To Do',
        ]);
    }

    /**
     * Test updating a task in API version 2.
     *
     * @return void
     */
    public function testUpdateTask()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->putJson("/api/tasks/update/{$task->id}", [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'To Do',
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Task', // Assuming you want to update the title
            'description' => 'Task description',
            'status' => 'To Do', // Assuming you want to update the status
        ]);
    }

    /**
     * Test deleting a task in API version 2.
     *
     * @return void
     */
    public function testDeleteTask()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/tasks/destroy/{$task->id}");

        $response->assertStatus(Response::HTTP_OK); // Change to HTTP_OK

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
