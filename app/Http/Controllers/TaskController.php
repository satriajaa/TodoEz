<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        $query = Task::query()
            ->where('user_id', auth()->id())
            ->with('category')
            ->latest();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($categoryId = request('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($priority = request('priority')) {
            $query->where('priority', $priority);
        }

        if (request()->has('status')) {
            $status = request('status') == 'completed';
            $query->where('status', $status);
        }

        $tasks = $query->paginate(request('perPage', 8));
        $categories = Category::where('user_id', auth()->id())->get();

        return view('tasks.index', compact('tasks', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('user_id', auth()->id())->get();
        return view('tasks.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'recurring_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_until' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if ($request->recurring_pattern) {
                if (!$request->deadline) {
                    $validator->errors()->add('deadline', 'Deadline wajib diisi untuk task berulang');
                }
                if (!$request->recurring_until) {
                    $validator->errors()->add('recurring_until', 'Tanggal terakhir wajib diisi untuk task berulang');
                }
                if (
                    $request->deadline && $request->recurring_until &&
                    $request->recurring_until <= $request->deadline
                ) {
                    $validator->errors()->add('recurring_until', 'Tanggal terakhir harus setelah deadline');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Task::create([
            ...$request->all(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $categories = Category::where('user_id', auth()->id())->get();
        return view('tasks.show', compact('task', 'categories'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        $categories = Category::where('user_id', auth()->id())->get();
        return view('tasks.edit', compact('task', 'categories'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'category_id' => 'nullable|exists:categories,id',
            'recurring_pattern' => 'nullable|in:daily,weekly,monthly,yearly',
            'recurring_until' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if ($request->recurring_pattern) {
                if (!$request->deadline) {
                    $validator->errors()->add('deadline', 'Deadline wajib diisi untuk task berulang');
                }
                if (!$request->recurring_until) {
                    $validator->errors()->add('recurring_until', 'Tanggal terakhir wajib diisi untuk task berulang');
                }
                if (
                    $request->deadline && $request->recurring_until &&
                    $request->recurring_until <= $request->deadline
                ) {
                    $validator->errors()->add('recurring_until', 'Tanggal terakhir harus setelah deadline');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $task->update($request->all());

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function toggleStatus(Task $task)
    {
        $this->authorize('update', $task);
        $task->update(['status' => !$task->status]);

        if ($task->status) {
            $this->handleRecurringTask($task);
        }

        return back()->with('success', 'Task status updated successfully.');
    }

    private function handleRecurringTask(Task $completedTask)
    {
        if (!$completedTask->recurring_pattern || !$completedTask->deadline) return;

        try {
            $newDeadline = match ($completedTask->recurring_pattern) {
                'daily' => $completedTask->deadline->addDay(),
                'weekly' => $completedTask->deadline->addWeek(),
                'monthly' => $completedTask->deadline->addMonth(),
                'yearly' => $completedTask->deadline->addYear(),
                default => null,
            };

            if ($newDeadline && $newDeadline <= $completedTask->recurring_until) {
                Task::create([
                    'title' => $completedTask->title,
                    'description' => $completedTask->description,
                    'deadline' => $newDeadline,
                    'category_id' => $completedTask->category_id,
                    'priority' => $completedTask->priority,
                    'recurring_pattern' => $completedTask->recurring_pattern,
                    'recurring_until' => $completedTask->recurring_until,
                    'status' => false,
                    'user_id' => $completedTask->user_id,
                ]);
            }
        } catch (\Exception $e) {
            logger()->error('Recurring task failed: ' . $e->getMessage());
        }
    }
}
