<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticatedUser()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ]);

        return $user;
    }

    public function test_authenticated_user_can_create_task()
    {
        $user = $this->authenticatedUser();

        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'priority' => 'high',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'title', 'description', 'priority', 'due_date', 'user_id']
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id,
            'priority' => 'high',
        ]);
    }

    public function test_authenticated_user_can_view_their_tasks()
    {
        $user = $this->authenticatedUser();
        $otherUser = User::factory()->create();

        // Create tasks for authenticated user
        Task::factory(3)->create(['user_id' => $user->id]);

        // Create tasks for other user (should not appear)
        Task::factory(2)->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'priority', 'user_id']
                ]
            ]);

        // Should only see own tasks
        $tasks = $response->json('data');
        $this->assertCount(3, $tasks);

        foreach ($tasks as $task) {
            $this->assertEquals($user->id, $task['user_id']);
        }
    }

    public function test_authenticated_user_can_view_single_task()
    {
        $user = $this->authenticatedUser();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'description', 'priority', 'user_id']
            ])
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                ]
            ]);
    }

    public function test_authenticated_user_cannot_view_others_task()
    {
        $this->authenticatedUser();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_update_their_task()
    {
        $user = $this->authenticatedUser();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Task Title',
            'priority' => 'low',
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'title', 'priority']
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Updated Task Title',
                    'priority' => 'low',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'priority' => 'low',
        ]);
    }

    public function test_authenticated_user_can_delete_their_task()
    {
        $user = $this->authenticatedUser();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Task deleted successfully']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_authenticated_user_can_complete_task()
    {
        $user = $this->authenticatedUser();
        $task = Task::factory()->create(['user_id' => $user->id, 'finish_date' => null]);

        $response = $this->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'finish_date']
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);

        $task->refresh();
        $this->assertNotNull($task->finish_date);
    }

    public function test_authenticated_user_can_mark_task_incomplete()
    {
        $user = $this->authenticatedUser();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'finish_date' => now()
        ]);

        $response = $this->patchJson("/api/tasks/{$task->id}/incomplete");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'finish_date']
            ]);

        $task->refresh();
        $this->assertNull($task->finish_date);
    }

    public function test_task_creation_validation_fails_with_invalid_data()
    {
        $this->authenticatedUser();

        $response = $this->postJson('/api/tasks', [
            'title' => '', // Required field
            'priority' => 'invalid', // Must be high, medium, or low
            'due_date' => 'invalid-date', // Must be valid date
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'priority', 'due_date']);
    }

    public function test_unauthenticated_user_cannot_access_tasks()
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'priority' => 'high',
        ]);
        $response->assertStatus(401);
    }

    public function test_user_can_filter_tasks_by_priority()
    {
        $user = $this->authenticatedUser();

        Task::factory()->create(['user_id' => $user->id, 'priority' => 'high']);
        Task::factory()->create(['user_id' => $user->id, 'priority' => 'medium']);
        Task::factory()->create(['user_id' => $user->id, 'priority' => 'low']);

        $response = $this->getJson('/api/tasks?priority=high');

        $response->assertStatus(200);
        $tasks = $response->json('data');

        $this->assertCount(1, $tasks);
        $this->assertEquals('high', $tasks[0]['priority']);
    }

    public function test_user_can_filter_tasks_by_status()
    {
        $user = $this->authenticatedUser();

        Task::factory()->create(['user_id' => $user->id, 'finish_date' => null]);
        Task::factory()->create(['user_id' => $user->id, 'finish_date' => now()]);

        $response = $this->getJson('/api/tasks?status=completed');
        $response->assertStatus(200);
        $completedTasks = $response->json('data');
        $this->assertCount(1, $completedTasks);

        $response = $this->getJson('/api/tasks?status=pending');
        $response->assertStatus(200);
        $pendingTasks = $response->json('data');
        $this->assertCount(1, $pendingTasks);
    }
}
