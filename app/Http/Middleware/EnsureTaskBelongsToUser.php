<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTaskBelongsToUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth()->user();
        $task = Task::where('id', $request->id)->first();

        if ($task == null)
            return response()->json(['message' => 'Task not found.'], 404);

        if ($task->user_id != $user->id)
            return response()->json(['message' => 'Task does not belong to you.'], 401);

        return $next($request);
    }
}
