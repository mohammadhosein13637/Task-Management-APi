<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportTasksRequest;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use League\Csv\Reader;
use League\Csv\Exception as CsvException;

class ImportController extends Controller
{

    /**
     * Import tasks from CSV file.
     */
    public function importTasks(ImportTasksRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();
            $importedCount = 0;
            $errors = [];
            $rowNumber = 1; // Start from 1 (header is row 0)

            foreach ($records as $offset => $record) {
                $rowNumber++;

                try {
                    // Validate required fields
                    if (empty($record['title'])) {
                        $errors[] = "Row {$rowNumber}: Title is required";
                        continue;
                    }

                    // Validate priority
                    $priority = strtolower(trim($record['priority'] ?? 'medium'));
                    if (!in_array($priority, ['high', 'medium', 'low'])) {
                        $priority = 'medium';
                    }

                    // Parse dates
                    $dueDate = null;
                    $finishDate = null;

                    if (!empty($record['due_date'])) {
                        try {
                            $dueDate = Carbon::parse($record['due_date'])->format('Y-m-d');
                        } catch (\Exception $e) {
                            $errors[] = "Row {$rowNumber}: Invalid due_date format";
                            continue;
                        }
                    }

                    if (!empty($record['finish_date'])) {
                        try {
                            $finishDate = Carbon::parse($record['finish_date'])->format('Y-m-d');
                        } catch (\Exception $e) {
                            $errors[] = "Row {$rowNumber}: Invalid finish_date format";
                            continue;
                        }
                    }

                    // Create task
                    Task::create([
                        'title' => trim($record['title']),
                        'description' => trim($record['description'] ?? ''),
                        'priority' => $priority,
                        'due_date' => $dueDate,
                        'finish_date' => $finishDate,
                        'user_id' => $request->user()->id,
                    ]);

                    $importedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            return response()->json([
                'message' => 'CSV import completed',
                'imported_count' => $importedCount,
                'errors' => $errors,
                'total_errors' => count($errors),
            ]);

        } catch (CsvException $e) {
            return response()->json([
                'message' => 'Error reading CSV file',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during import',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get CSV template.
     */
    public function getTemplate(): JsonResponse
    {
        $template = [
            'headers' => [
                'title',
                'description',
                'priority',
                'due_date',
                'finish_date'
            ],
            'example_row' => [
                'title' => 'Complete project documentation',
                'description' => 'Write comprehensive documentation for the project',
                'priority' => 'high',
                'due_date' => '2024-12-31',
                'finish_date' => ''
            ],
            'instructions' => [
                'title: Required field, maximum 255 characters',
                'description: Optional field, any text',
                'priority: Optional field, must be one of: high, medium, low (default: medium)',
                'due_date: Optional field, format: YYYY-MM-DD',
                'finish_date: Optional field, format: YYYY-MM-DD (leave empty for incomplete tasks)'
            ]
        ];

        return response()->json($template);
    }
}
