<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $tasks = $user->isAdmin() ? Task::with('assignedUser')->latest()->get() : $user->tasks()->latest()->get();

        return response()->json($tasks, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'deadline' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $task = Task::create($request->only(['title', 'description', 'assigned_to', 'deadline']));

        // Optionally send notification
        $assignedUser = User::find($request->assigned_to);
        // Mail::to($assignedUser->email)->send(new TaskAssigned($task)); // ← implement this later

        return response()->json(['message' => 'Task created successfully', 'task' => $task], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $task = Task::with('assignedUser')->find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();

        if (!$user->isAdmin() || $user->id === $task->assigned_to) {
            return response()->json($task, Response::HTTP_OK);
        }

        return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();

        if($user->isAdmin()) {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:pending,in_progress,completed',
                'assigned_to' => 'sometimes|required|exists:users,id',
                'deadline' => 'sometimes|required|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $task->update($request->only(['title', 'description', 'status', 'assigned_to', 'deadline']));

            return response()->json(['message' => 'Task updated successfully', 'task' => $task], Response::HTTP_OK);
        } elseif($user->id === $task->assigned_to) {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,in_progress,completed',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $task->update(['status' => $request->status]);
            return response()->json(['message' => 'Task status updated successfully', 'task' => $task], Response::HTTP_OK);
        }

        return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);

    }

    public function destroy($id) 
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $user = Auth::user();

        if ($user->isAdmin() || $user->id === $task->assigned_to) {
            $task->delete();
            return response()->json(['message' => 'Task deleted successfully'], Response::HTTP_OK);
        }

        return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
    }


}
