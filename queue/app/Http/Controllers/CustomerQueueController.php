<?php

namespace App\Http\Controllers;

use App\Models\CustomerQueue;
use App\Models\User;
use App\Models\Counter;
use Illuminate\Http\Request;
use Carbon\Carbon;

// try test
class CustomerQueueController extends Controller
{
    public function store(Request $request)
    {
        // Validate request data
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'counter_id' => 'required|exists:counters,id',
        ]);

        // Get today's date
        $today = Carbon::today()->toDateString();

        // Count the number of queues for today
        $queueCount = CustomerQueue::whereDate('created_at', $today)->count();

        // Generate the queue number (e.g., Q001, Q002, ...)
        $queueNumber = '1' . str_pad($queueCount + 1, 3, '0', STR_PAD_LEFT);

       // Set the current queue to the next available number after the last serviced queue
        $lastServicedQueue = CustomerQueue::whereNotNull('serviced_at')
        ->orderBy('created_at', 'desc')
        ->first();

        $currentQueue = $lastServicedQueue ? $lastServicedQueue->current_queue + 1 : 1001;

        // Create the customer queue
        $customerQueue = CustomerQueue::create([
            'user_id' => $request->user_id,
            'counter_id' => $request->counter_id,
            'queue_number' => $queueNumber,
            'current_queue' => $currentQueue,
            'joined_at' => now(),
            'last_generated_at' => now(),
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
        // Find the last serviced queue
        $lastServicedQueue = CustomerQueue::whereNotNull('serviced_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastServicedQueue) {
            // Check if the last serviced queue was created on the current date
            $isSameDay = Carbon::parse($lastServicedQueue->created_at)->isSameDay(Carbon::today());

            if (!$isSameDay) {
                // If the last serviced queue is from a different day, set current_queue to 1001 for new entries
                CustomerQueue::whereNull('serviced_at')
                    ->whereDate('created_at', Carbon::today())
                    ->update(['current_queue' => 1001]);
            } else {
                // Increment the current queue for all subsequent unserviced queues from the same day
                $unservicedQueues = CustomerQueue::whereNull('serviced_at')
                    ->whereDate('created_at', Carbon::today())
                    ->get();
                foreach ($unservicedQueues as $unservicedQueue) {
                    $unservicedQueue->update(['current_queue' => $lastServicedQueue->current_queue + 1]);
                }
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
    public function markAsServiced(string $queueNumber)
    {
        $queue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$queue) {
            return response()->json([
                'error' => 'Queue not found',
            ], 404);
        }

        $queue->update(['serviced_at' => now()]);
        $this->updateCurrentQueue();

        $updatedQueue = CustomerQueue::where('queue_number', $queueNumber)
            ->whereDate('created_at', Carbon::today())
            ->first();

        return response()->json([
            'message' => 'Queue marked as serviced successfully',
            'queue' => $updatedQueue
        ], 200);
    }
}
