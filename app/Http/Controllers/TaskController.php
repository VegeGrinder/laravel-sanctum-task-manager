<?php

namespace App\Http\Controllers;

use App\Enums\TagType;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $validateSort = Validator::make(
            request()->all(),
            [
                'sort_by' => 'required_with:sort_direction',
                'sort_direction' => 'required_with:sort_by',
            ]
        );

        if ($validateSort->fails()) {
            return response()->json([
                'message' => 'Please check your inputs.',
                'errors' => $validateSort->errors(),
            ], 422);
        }

        $user = Auth()->user();
        $tasks = Task::where('user_id', $user->id)->listing()->get();

        return response()->json(['tasks' => $tasks]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validateTask = Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'description' => 'required',
                    'tags' => 'sometimes|array',
                    'priority_level' => 'sometimes|integer',
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::findOrFail($id);

        return response()->json($task, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validateTask = Validator::make(
                $request->all(),
                [
                    'title' => 'sometimes|required',
                    'description' => 'sometimes|required',
                    'tags' => 'sometimes|array',
                    'priority_level' => 'sometimes|integer',
                    'due_date' => 'sometimes|date_format:Y-m-d|after_or_equal:' . now()->format('Y-m-d'),
                    'is_completed' => 'sometimes|boolean',
                    'is_archived' => 'sometimes|boolean',
                ]
            );

            if ($validateTask->fails()) {
                return response()->json([
                    'message' => 'Please check your inputs.',
                    'errors' => $validateTask->errors(),
                ], 401);
            }

            $validated = $validateTask->validated();

            if (isset($validated['tags'])) {
                foreach ($validated['tags'] as $tag) {
                    if (!in_array($tag, TagType::getValues())) {
                        return response()->json([
                            'message' => 'Invalid tag found.',
                        ], 401);
                    }
                }
            }

            $task = Task::find($id);

            if ($task == null)
                return response()->json(['message' => 'Task not found'], 404);

            if (isset($validated['is_completed']))
                $validated['completed_date'] = now();

            if (isset($validated['is_archived']))
                $validated['archived_date'] = now();

            $task->update($validated);

            return response()->json(['message' => 'Task updated.', 'task' => $task], 202);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $task = Task::find($id);

            if ($task == null)
                return response()->json(['message' => 'Task not found.'], 401);
            else
                $task->delete();

            return response()->json(['message' => 'Task deleted']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
