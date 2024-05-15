<?php

namespace App\Http\Controllers;

use App\Models\CustomerQueue;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerQueueController extends Controller
{

    public function store(Request $request)
    {
        // Validate request data
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Get the department
        $department = Department::find($request->department_id);

        if (!$department) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        // Get today's date
        $today = Carbon::today()->toDateString();

        // Get the maximum queue number for the department
        $maxQueueNumber = CustomerQueue::where('department_id', $department->id)
            ->whereDate('created_at', $today)
            ->max('queue_number');

        // Generate the queue number based on department
        $queueNumber = $maxQueueNumber ? $maxQueueNumber + 1 : ($department->id * 1000) + 1;

       // Find the last serviced queue for this department
        $lastServicedQueue = CustomerQueue::where('department_id', $department->id)
        ->whereNotNull('serviced_at')
        ->orderBy('created_at', 'desc')
        ->first();

        if ($lastServicedQueue) {
            $currentQueue = $lastServicedQueue->queue_number;
            $nextQueueNumber = CustomerQueue::where('department_id', $department->id)
            ->whereDate('created_at', $today)
            ->whereNull('serviced_at')
            ->where('queue_number', '>', $currentQueue)
            ->min('queue_number');
        $nextQueue = $nextQueueNumber ?: null;
        } else {
            // If no queues have been serviced for this department yet,
            // set both current_queue and next_queue to null
            $currentQueue = null;
            $nextQueue = null;
        }

        // Create the customer queue with current_queue and next_queue values
        $customerQueue = CustomerQueue::create([
            'user_id' => $request->user_id,
            'department_id' => $request->department_id,
            'queue_number' => $queueNumber,
            'current_queue' => $currentQueue,
            'next_queue' => $nextQueue,
            'joined_at' => now(),
        ]);

        // Update current queue
        $this->updateCurrentQueue();
        $this->updateLastResetDate();

        // Return the generated queue number and average service time
        return response()->json([
            'queue_number' => $queueNumber,
            //'average_service_time' => $averageServiceTime,
        ], 201);
    }

    private function updateCurrentQueue()
    {
        // Get all departments
        $departments = Department::all();

        foreach ($departments as $department) {
            // Find the last serviced queue for this department
            $lastServicedQueue = CustomerQueue::where('department_id', $department->id)
                ->whereNotNull('serviced_at')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastServicedQueue) {
                // Get the next queue number after the last serviced queue
                $nextQueueNumber = CustomerQueue::where('department_id', $department->id)
                    ->whereDate('created_at', $lastServicedQueue->created_at)
                    ->whereNull('serviced_at')
                    ->where('queue_number', '>', $lastServicedQueue->queue_number)
                    ->min('queue_number');

                // Update the current queue for all subsequent unserviced queues for this department
                CustomerQueue::where('department_id', $department->id)
                    ->whereNull('serviced_at')
                    ->where('queue_number', '>=', $nextQueueNumber)
                    ->update(['current_queue' => $lastServicedQueue->queue_number, 'next_queue' => $nextQueueNumber]);
                } else {

                // If no queues have been serviced for this department yet,
                // set current queue to null
                CustomerQueue::where('department_id', $department->id)
                ->update(['current_queue' => null, 'next_queue' => null]);
            }
        }
    }

    private function updateLastResetDate()
    {
        // Get yesterday's date
        $today = Carbon::today()->toDateString();

        // Check if there are any queues created on yesterday's date
        $todayQueuesCount = CustomerQueue::whereDate('created_at', $today)->count();

        if ($todayQueuesCount > 0) {
            // If there are no queues created on yesterday's date, update the last_reset_date column
            CustomerQueue::whereDate('created_at', $today)->update(['last_reset_date' => $today]);
        }
    }

    public function callQueue(string $queueNumber, int $departmentId, int $counterId)
    {
        $queue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->whereNull('serviced_at')
            ->first();

        if (!$queue) {
            return response()->json([
                'error' => 'Queue not found or already served',
            ], 404);
        }

        // Update the queue with the counter ID and serviced timestamp
        $queue->update([
            'serviced_at' => now(),
            'counter_id' => $counterId,
        ]);

        // Get the next queue in line based on created_at timestamp
        $nextQueue = CustomerQueue::where('department_id', $departmentId)
            ->whereDate('created_at', Carbon::today())
            ->whereNull('serviced_at')
            ->where('created_at', '>', $queue->created_at)
            ->orderBy('created_at', 'asc')
            ->first();

        $nextQueueNumber = $nextQueue ? $nextQueue->queue_number : null;

        // Update the current_queue and next_queue for all unserviced queues
        CustomerQueue::where('department_id', $departmentId)
            ->whereNull('serviced_at')
            ->update([
                'current_queue' => $queueNumber,
                'next_queue' => $nextQueueNumber
            ]);

        $updatedQueue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->first();

        return response()->json([
            'message' => 'Calling for ' . $queueNumber,
            'queue' => $updatedQueue
        ], 200);
    }

    public function passQueue(Request $request, string $queueNumber, int $newDepartmentId)
    {
        // Find the original queue
        $originalQueue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$originalQueue) {
            return response()->json([
                'error' => 'Queue not found',
            ], 404);
        }

        // Get the new department
        $newDepartment = Department::find($newDepartmentId);

        if (!$newDepartment) {
            return response()->json(['error' => 'Department not found'], 404);
        }

        // Set the department property
        $this->department = $newDepartment;

        // Create a new queue for the new department with the same customer_id and 
        // queue_number as the original queue
        $newQueue = new CustomerQueue([
            'user_id' => $originalQueue->user_id,
            'queue_number' => $originalQueue->queue_number,
            'department_id' => $newDepartment->id,
        ]);

        // Set the joined_at value for the new queue
        $newQueue->joined_at = now();

        // Save the new queue
        $newQueue->save();

        // Update the current queue for the new department
        $this->updateCurrentQueue();

        $updatedQueue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->first();

        return response()->json([
            'message' => 'Queue passed to ' . $newDepartment->name,
            'queue' => $updatedQueue
        ], 200);
    }
}
