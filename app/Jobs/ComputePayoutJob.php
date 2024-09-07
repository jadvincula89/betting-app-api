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

class ComputePayoutJob implements ShouldQueue
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
            $fight = Fights::where('status', '=', 0)->findorFail($data['fight_no']);
            $totalMeronBet = Tickets::where('fight_id', '=', $data['fight_no'])
                ->where('team', '=', Fights::MERON)
                ->sum('amount_bet');
            $totalWalaBet = Tickets::where('fight_id', '=', $data['fight_no'])
                ->where('team', '=', Fights::WALA)
                ->sum('amount_bet');
            $tickets = Tickets::where('fight_id', '=', $data['fight_no'])->get();
            if ($tickets && $fight) {
                foreach($tickets as $t) {

                    $payout=PayoutServices::payout_calc($fight->bet_percentage,  $totalMeronBet,   $totalWalaBet , $t->team, $t->amount_bet);
                    $share=PayoutServices::share_calc($fight->bet_percentage,  $totalMeronBet,   $totalWalaBet , $t->team, $t->amount_bet);
                    DB::table('tickets')->where('id',$t->id)->update(['amount_claimed'=>$payout,'shares'=>$share]);
               
            }
                return  0;

            }

        } catch (Exception $e) {
            Log::error('Job failed: ' . $e->getMessage());
        }
    }
}
