<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CloseProjectRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId;

    /**
     * Create a new job instance.
     */
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $project = Project::findOrFail($this->projectId);

            $requests = $project->requests()->get();

            foreach ($requests as $request) {
                $request->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

            }

            $project->update(['status' => 'closed']);

        
        } catch (\Exception $e) {
          
            throw $e;
        }
    }
}