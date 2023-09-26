<?php

namespace App\Http\Controllers;

use App\Enums\TagType;
use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of Tasks.
     */
    public function index(Request $request)
    {
        try {
            $validateSort = Validator::make(
                $request->all(),
                [
                    'title' => 'sometimes|string',
                    'description' => 'sometimes|string',
                    /**
                     * Urgent=3;High=2;Normal=1;Low=0;
                     */
                    'priority_level' => 'sometimes|integer',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'due_date_from' => 'sometimes|date_format:Y-m-d',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'due_date_to' => 'sometimes|date_format:Y-m-d',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'completed_date_from' => 'sometimes|date_format:Y-m-d',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'completed_date_to' => 'sometimes|date_format:Y-m-d',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'archived_date_from' => 'sometimes|date_format:Y-m-d',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'archived_date_to' => 'sometimes|date_format:Y-m-d',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'created_date_from' => 'sometimes|date_format:Y-m-d',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'created_date_to' => 'sometimes|date_format:Y-m-d',
                    'sort_by' => 'sometimes|required_with:sort_direction|string',
                    /**
                     * Either 'ASC' or 'DESC'.
                     */
                    'sort_direction' => 'sometimes|required_with:sort_by|string',
                ]
            );

            if ($validateSort->fails()) {
                return response()->json([
                    'message' => 'Please check your inputs.',
                    'errors' => $validateSort->errors(),
                ], 422);
            }

            $user = Auth()->user();
            $tasks = Task::with('taskFiles')
                ->where('user_id', $user->id)
                ->listing()
                ->paginate(config('tasks.pagination_length'))
                ->appends(request()->query());

            return response()->json(['tasks' => $tasks]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new Task.
     */
    public function store(Request $request)
    {
        try {
            $validateTask = Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'description' => 'required',
                    /**
                     * 'In-progress' or 'Blocked' or 'On-hold'.
                     */
                    'tags' => 'sometimes|array',
                    /**
                     * Urgent=3;High=2;Normal=1;Low=0;
                     */
                    'priority_level' => 'sometimes|integer',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'due_date' => 'sometimes|date_format:Y-m-d|after_or_equal:' . now()->format('Y-m-d'),
                ]
            );

            if ($validateTask->fails()) {
                return response()->json([
                    'message' => 'Please check your inputs.',
                    'errors' => $validateTask->errors(),
                ], 422);
            }

            $task = Task::create(
                [...$validateTask->validated(), 'user_id' => $request->user()->id]
            );

            return response()->json([
                'message' => 'Task created.',
                'task' => $task,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified Task.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validateTask = Validator::make(
                $request->all(),
                [
                    'title' => 'sometimes|required',
                    'description' => 'sometimes|required',
                    /**
                     * 'In-progress' or 'Blocked' or 'On-hold'.
                     */
                    'tags' => 'sometimes|array',
                    /**
                     * Urgent=3;High=2;Normal=1;Low=0;
                     */
                    'priority_level' => 'sometimes|integer',
                    /**
                     * Date in YYYY-MM-DD format.
                     * @example 2023-09-27
                     */
                    'due_date' => 'sometimes|date_format:Y-m-d|after_or_equal:' . now()->format('Y-m-d'),
                    'is_completed' => 'sometimes|boolean',
                    'is_archived' => 'sometimes|boolean',
                ]
            );

            if ($validateTask->fails()) {
                return response()->json([
                    'message' => 'Please check your inputs.',
                    'errors' => $validateTask->errors(),
                ], 422);
            }

            $validated = $validateTask->validated();

            if (isset($validated['tags'])) {
                foreach ($validated['tags'] as $tag) {
                    if (!in_array($tag, TagType::getValues())) {
                        return response()->json([
                            'message' => 'Invalid tag found.',
                        ], 422);
                    }
                }
            }

            $task = Task::find($id);

            if ($task == null)
                return response()->json(['message' => 'Task not found'], 404);

            if (isset($validated['is_completed']) && $validated['is_completed'] == true)
                $validated['completed_date'] = now();
            else
                $validated['completed_date'] = null;

            if (isset($validated['is_archived']) && $validated['is_archived'] == true)
                $validated['archived_date'] = now();
            else
                $validated['archived_date'] = null;

            $task->update($validated);

            return response()->json(['message' => 'Task updated.', 'task' => $task], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified Task.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $task = Task::find($id);

            if ($task == null)
                return response()->json(['message' => 'Task not found.'], 404);
            else {
                $taskFiles = TaskFile::where('task_id', $task->id)->get();

                foreach ($taskFiles as $taskFile) {
                    Storage::delete($taskFile->path);
                    $taskFile->delete();
                }

                $task->delete();
            }

            DB::commit();
            return response()->json(['message' => 'Task deleted'], 204);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload a file to the server
     */
    public function uploadFile(Request $request, string $id)
    {
        try {
            $validateFile = Validator::make(
                $request->all(),
                [
                    /**
                     * MIME types allowed: svg,png,jpb,mp4,csv,txt,doc,docx.
                     */
                    'file' => 'required|file|mimes:mimes:svg,png,jpb,mp4,csv,txt,doc,docx',
                ]
            );

            if ($validateFile->fails()) {
                return response()->json([
                    'message' => 'File upload failed.',
                    'errors' => $validateFile->errors(),
                ], 401);
            }

            $filename = $request->file('file')->getClientOriginalName();

            $taskFile = TaskFile::create([
                'task_id' => $id,
                'path' => $request->file('file')->storeAs('uploads/' . Auth()->user()->id . "/tasks/$id/files", $filename),
                'filename' => $filename,
            ]);

            return response()->json(['message' => 'File upload completed.', 'taskFile' => $taskFile]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a file from the server
     */
    public function downloadFile(string $id, string $imageId)
    {
        // Already checked Task $id owned by User through EnsureTaskBelongsToUser middleware
        $taskFile = TaskFile::find($imageId);
        $directory = "app/$taskFile->path";

        if ($taskFile == null || Storage::exists($directory))
            return response()->json(['message' => 'File not found.'], 404);

        return response()->download(storage_path($directory), $taskFile->filename);
    }

    /**
     * Delete a file from the server
     */
    public function deleteFile(string $id, string $imageId)
    {
        // Already checked Task $id owned by User through EnsureTaskBelongsToUser middleware
        $taskFile = TaskFile::find($imageId);
        $directory = "app/$taskFile->path";

        if ($taskFile == null || Storage::exists($directory))
            return response()->json(['message' => 'File not found.'], 404);

        Storage::delete($taskFile->path);
        $taskFile->delete();

        return response()->json(['message' => 'File deleted.']);
    }
}
