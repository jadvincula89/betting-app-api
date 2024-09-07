<?php
namespace App\Services;

use App\Models\Fights;
use App\Models\Tickets;
use Illuminate\Support\Facades\DB;
use App\Repositories\SalesRepository;

class PayoutServices
{
    protected $salesRepository;
    public function __construct(SalesRepository $salesRepository)
    {
        $this->salesRepository = $salesRepository;
    }
    public static function payout($ticket_num)
    {

        try {
            $ticket = Tickets::where('ticket_no', '=', $ticket_num)->first();
            $meron_total_bet = 0;
            $wala_total_bet = 0;
            $percentage = 0;
            if ($ticket->is_claimed == Tickets::NOT_CLAIM) {
                $fight = Fights::where('id', '=', $ticket->fight_id)
                    ->where('status', '=', Fights::CLOSE)->first();
                if ($fight) {
                    if ($fight->winner == Fights::TABLA) {
                        return $ticket->amount_bet;
                    }
                    if ($fight->winner == Fights::CANCEL) {
                        return $ticket->amount_bet;
                    }
                    $percentage = 1 - ($fight->bet_percentage / 100);

                    if ((int) $ticket->team === (int) $fight->winner) {

                        $results = Tickets::select('team', DB::raw('COALESCE(SUM(amount_bet),0) as amount'))
                            ->where('fight_id', $fight->id)
                            ->groupBy('team')
                            ->get();

                        foreach ($results as $key) {
                            if ($key['team'] == Fights::MERON) {
                                $meron_total_bet = $key['amount'] > 0 ? $key['amount'] : 0;
                            }
                            if ($key['team'] == Fights::WALA) {
                                $wala_total_bet = $key['amount'] > 0 ? $key['amount'] : 0;
                            }
                        }

                        if ($ticket->team == Fights::MERON) {
                            $win = ($wala_total_bet * $percentage);
                            $total_bet = ($meron_total_bet);
                            $my_bet = ($ticket->amount_bet);
                            $payout = ($my_bet / $total_bet * $win) + $my_bet;
                            return ($payout);
                        }
                        if ($ticket->team == Fights::WALA) {
                            $win = ($meron_total_bet * $percentage);
                            $total_bet = ($wala_total_bet);
                            $my_bet = ($ticket->amount_bet);
                            $payout = ($my_bet / $total_bet * $win) + $my_bet;

                            return ($payout);
                        }

                    }
                    return 0;

                }
            }

        } catch (\Exception $e) {
            return $e->getMessage();

        }

    }
    public  function sales_summary($request)
    {
       return $this->salesRepository->summary($request);
    }
    public static function share_calc($percent, $meron_total_bet, $wala_total_bet, $team, $bet)
    {

        try {

            $percentage =  ($percent / 100);

            if ($team == Fights::MERON && $meron_total_bet > 0) {
                $win = ($wala_total_bet * $percentage);
                $total_bet = ($meron_total_bet);
                $my_bet = ($bet);
                // $payout=$my_bet / $total_bet*  $win;
                $share = ($my_bet / $total_bet * $win) ;
                return ($share);
            }
            if ($team == Fights::WALA && $wala_total_bet > 0) {
                $win = ($meron_total_bet * $percentage);
                $total_bet = ($wala_total_bet);
                $my_bet = ($bet);
                // $payout=$my_bet / $total_bet*  $win;
                $payout = ($my_bet / $total_bet * $win);
                return ($payout);
            }
            return 0;

        } catch (\Exception $e) {
            return $e->getMessage();

        }

    }
    public static function payout_calc($percent, $meron_total_bet, $wala_total_bet, $team, $bet)
    {

        try {

            $percentage = 1 - ($percent / 100);

            if ($team == Fights::MERON && $meron_total_bet > 0) {
                $win = ($wala_total_bet * $percentage);
                $total_bet = ($meron_total_bet);
                $my_bet = ($bet);
                // $payout=$my_bet / $total_bet*  $win;
                $payout = ($my_bet / $total_bet * $win) + $my_bet;
                return ($payout);
            }
            if ($team == Fights::WALA && $wala_total_bet > 0) {
                $win = ($meron_total_bet * $percentage);
                $total_bet = ($wala_total_bet);
                $my_bet = ($bet);
                // $payout=$my_bet / $total_bet*  $win;
                $payout = ($my_bet / $total_bet * $win) + $my_bet;
                return ($payout);
            }
            return 0;

        } catch (\Exception $e) {
            return $e->getMessage();

        }

    }

}
