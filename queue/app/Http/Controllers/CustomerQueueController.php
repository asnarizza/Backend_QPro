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

        // Count the number of queues for today
        $queueCount = CustomerQueue::whereDate('created_at', $today)
            ->where('department_id', $department->id)
            ->count();

        // Generate the queue number based on department
        $queueNumber = ($department->id * 1000) + $queueCount + 1;

        // Get the current queue for the department
        $currentQueue = ($department->id * 1000) + 1;

    //    // Set the current queue to the next available number after the last serviced queue
    //     $lastServicedQueue = CustomerQueue::whereNotNull('serviced_at')
    //     ->orderBy('created_at', 'desc')
    //     ->first();

    //     $currentQueue = $lastServicedQueue ? $lastServicedQueue->current_queue + 1 : 1001;

        // Create the customer queue
        $customerQueue = CustomerQueue::create([
            'user_id' => $request->user_id,
            'department_id' => $request->department_id,
            'queue_number' => $queueNumber,
            'current_queue' => $currentQueue,
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

    // private function updateCurrentQueue()
    // {
    //     // Find the last serviced queue
    //     $lastServicedQueue = CustomerQueue::whereNotNull('serviced_at')
    //         ->orderBy('created_at', 'desc')
    //         ->first();

    //     if ($lastServicedQueue) {
    //         // Check if the last serviced queue was created on the current date
    //         $isSameDay = Carbon::parse($lastServicedQueue->created_at)->isSameDay(Carbon::today());

    //         if (!$isSameDay) {
    //             // If the last serviced queue is from a different day, set current_queue to 1001 for new entries
    //             CustomerQueue::whereNull('serviced_at')
    //                 ->whereDate('created_at', Carbon::today())
    //                 ->update(['current_queue' => 1001]);
    //         } else {
    //             // Increment the current queue for all subsequent unserviced queues from the same day
    //             $unservicedQueues = CustomerQueue::whereNull('serviced_at')
    //                 ->whereDate('created_at', Carbon::today())
    //                 ->get();
    //             foreach ($unservicedQueues as $unservicedQueue) {
    //                 $unservicedQueue->update(['current_queue' => $lastServicedQueue->current_queue + 1]);
    //             }
    //         }
    //     }
    // }

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
                // Set current queue to the next available number after the last serviced queue
                $currentQueue = $lastServicedQueue->queue_number + 1;

                // Update the current queue for all subsequent unserviced queues for this department
                CustomerQueue::where('department_id', $department->id)
                    ->whereNull('serviced_at')
                    ->update(['current_queue' => $currentQueue]);
            } else {
                // If no queues have been serviced for this department yet, set current queue to department's base number
                $currentQueue = ($department->id * 1000) + 1;

                // Update the current queue for all queues for this department
                CustomerQueue::where('department_id', $department->id)
                    ->update(['current_queue' => $currentQueue]);
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

    // Example method to mark a queue as serviced using queue_number
    public function callQueue(string $queueNumber, int $counterId)
    {
        $queue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$queue) {
            return response()->json([
                'error' => 'Queue not found',
            ], 404);
        }

        // Update the queue with the counter ID and serviced timestamp
        $queue->update([
            'serviced_at' => now(),
            'counter_id' => $counterId,
        ]);

         // Update current queue
        $this->updateCurrentQueue();

        $updatedQueue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->first();

        return response()->json([
            'message' => 'Calling for ' . $queueNumber,
            'queue' => $updatedQueue
        ], 200);
    }

    // public function passQueue(Request $request, string $queueNumber, int $newDepartmentId)
    // {
    //     // Find the queue
    //     $queue = CustomerQueue::where('queue_number', $queueNumber)
    //         ->whereDate('created_at', Carbon::today())
    //         ->first();

    //     if (!$queue) {
    //         return response()->json([
    //             'error' => 'Queue not found',
    //         ], 404);
    //     }

    //     // Get the new department
    //     $newDepartment = Department::find($newDepartmentId);

    //     if (!$newDepartment) {
    //         return response()->json(['error' => 'Department not found'], 404);
    //     }

    //     // Update the queue with the new department ID
    //     $queue->update([
    //         'department_id' => $newDepartmentId,
    //     ]);

    //     // Update the current queue for the new department
    //     $this->updateCurrentQueue();

    //     $updatedQueue = CustomerQueue::where('queue_number', $queueNumber)
    //         ->whereDate('created_at', Carbon::today())
    //         ->first();

    //     return response()->json([
    //         'message' => 'Queue passed to ' . $newDepartment->name,
    //         'queue' => $updatedQueue
    //     ], 200);
    // }

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

        // Create a new queue for the new department with the same customer_id and queue_number as the original queue
        $newQueue = new CustomerQueue([
            'user_id' => $originalQueue->user_id,
            'queue_number' => $originalQueue->queue_number,
            'department_id' => $newDepartment->id,
        ]);

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
