<?php

namespace App\Jobs;

use App\Models\Fights;
use App\Models\Tickets;
use App\Services\PayoutServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SetTicketWinnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        try {
            $data = $this->data;
            Tickets::where('fight_id',  $data['fight_id'])->update(['winner' =>  $data['winner']]);

        } catch (Exception $e) {
            Log::error('Job failed: ' . $e->getMessage());
        }
    }
}
