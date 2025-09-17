<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     description="Task model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Complete project documentation"),
 *     @OA\Property(property="description", type="string", example="Write comprehensive documentation for the project"),
 *     @OA\Property(property="priority", type="string", enum={"high", "medium", "low"}, example="high"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2024-12-31"),
 *     @OA\Property(property="finish_date", type="string", format="date", nullable=true, example="2024-12-25"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'due_date',
        'finish_date',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'finish_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return !is_null($this->finish_date);
    }
}
