<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatTraits;
use App\Jobs\ComputePayoutJob;
use App\Jobs\SetTicketWinnerJob;
use App\Models\Configurations;
use App\Models\Events;
use App\Models\Fights;
use App\Models\Tickets;
use App\Models\Transactions;
use App\Services\PayoutServices;
use App\Services\TicketServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FightController extends Controller
{
    use FormatTraits;

    public function index(Request $request)
    {
        $perPage = $request->input('results', 200);
        if (!is_numeric($perPage)) {
            $perPage = 50;
        }
        $event = Events::where('isActive', '=', 1)->first();
        if ($event) {
            $fight = Fights::where('event_id', $event->id)->join('users', 'users.id', '=', 'fights.created_by')
                ->select('fights.*', 'users.name')
                ->orderBy('created_at', 'DESC')
                ->paginate($perPage);

            return response()->json([
                'pagination' => $this->pagination($fight),
                'data' => $fight->items(),
                'edit' => $event->isOpen == 1 ? true : false,

            ]);

        } else {
            return response()->json([
                'pagination' => [],
                'data' => [],
            ]);

        }

    }

    public function store(Request $request)
    {

        $event = Events::getFirstActiveAndOpenEvent();

        try {
            if ($event) {

                $user = auth()->user();
                $results = Fights::select(DB::raw('COUNT(id) fight_no'))
                    ->where('event_id', $event->id)
                    ->first();
                $fight_no = "000001";
                if (isset($results->fight_no) && $results->fight_no >= 1) {
                    $fight_no = sprintf("%06d", $results->fight_no + 1);
                }

                $config = Configurations::where('name', '=', 'bet_percentage')->first();

                Fights::create([
                    'event_id' => $event->id,
                    'total_meron_bet' => 0,
                    'total_wala_bet' => 0,
                    'status' => Fights::OPEN,
                    'bet_on_wala' => Fights::OPEN,
                    'bet_on_meron' => Fights::OPEN,
                    'fight_no' => $fight_no,
                    'start' => Fights::STOP,
                    'bet_percentage' => $config->value,
                    'created_by' => $user->id,
                ]);

                return response()->json([
                    'success' => true,
                    'count' => $config,
                    'message' => 'New match successfully added',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failure to saved record',
                ]);

            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e,
                'message' => $e->getMessage(),

            ]);

        }

    }
    public function fight_details()
    {
        try {
            $event = Events::getFirstActiveAndOpenEvent();
            $percentage = 1;
            $user = auth()->user();
            $fight = Fights::where('event_id', '=', $event->id)
                ->where('status', '=', Fights::OPEN)
                ->first();
            if ($fight) {
                $percentage = 1 - ($fight->bet_percentage / 100);

                $results = DB::table('tickets')
                    ->select('team')
                    ->selectRaw('COALESCE(SUM(amount_bet), 0) as amount')
                    ->selectRaw('SUM(CASE WHEN created_by = ? THEN amount_bet ELSE 0 END) as per_fight_collection',[$user->id])
                    ->where('fight_id', $fight->id)
                    ->groupBy('team')
                    ->get();
                    
                $total_meron_winnable = 0;
                $total_wala_winnable = 0;
                $meron_total_bet = 0;
                $wala_total_bet = 0;
                $per_teller_collection_m=0;
                $per_teller_collection_w=0;
                if ($results) {

                  
                    foreach ($results as $key) {
                        if ($key->team == 1) {
                            $meron_total_bet = $key->amount > 0 ? $key->amount : 0;
                             $per_teller_collection_m = $key->per_fight_collection;
                        }
                        if ($key->team == 2) {
                            $wala_total_bet = $key->amount > 0 ? $key->amount : 0;
                            $per_teller_collection_w = $key->per_fight_collection;
                        }
                    }
                    $total_meron_winnable = $meron_total_bet > 0 ? $meron_total_bet + ($wala_total_bet * $percentage) : 0;
                    $meron_payout = $total_meron_winnable > 0 ? $total_meron_winnable * 100 / $meron_total_bet : 0;
                    $total_wala_winnable = $wala_total_bet > 0 ? $wala_total_bet + ($meron_total_bet * $percentage) : 0;
                    $wala_payout = $total_wala_winnable > 0 ? $total_wala_winnable * 100 / $wala_total_bet : 0;
                }

                $fight['meron_payout'] = PayoutServices::payout_calc($fight->bet_percentage, $meron_total_bet, $wala_total_bet, Fights::MERON, 100);
                $fight['wala_payout'] = PayoutServices::payout_calc($fight->bet_percentage, $meron_total_bet, $wala_total_bet, Fights::WALA, 100);
                $fight['total_meron_bet'] = $meron_total_bet;
                
               $fight['per_teller_collection_m'] = $per_teller_collection_m;
               $fight['per_teller_collection_w'] = $per_teller_collection_w;

                $fight['total_wala_bet'] = $wala_total_bet;
                $data = [];
                $data['event'] = $event;
                $data['fight'] = $fight;
                $data['results'] = $results;
                return response()->json([
                    'success' => true,
                    'data' => $data,

                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),

            ]);

        }
    }
    public function updatewinner(Request $request, $id)
    {

        try {
            $Fight = Fights::findOrFail($id);
            if ($Fight) {

                $params = (array) $request->all();
                $user = auth()->user();
                $Fight->winner = $request->winner;
                $Fight->status = Fights::CLOSE;
                $Fight->updated_at = date("Y-m-d H:i:s");
                $Fight->updated_by = $user->id;
                $Fight->save();
                SetTicketWinnerJob::dispatch(['fight_id' => $id, 'winner' => $request->winner]);
                if ($request->winner == 3) {
                    return response()->json([
                        'success' => true,
                        'message' => "Match was set to DRAW",
                    ]);
                }
                if ($request->winner == 1 || $request->winner == 2) {
                    return response()->json([
                        'success' => true,
                        'message' => "Successfully updated WINNER of Fight",
                    ]);
                }
                if ($request->winner == 0) {
                    return response()->json([
                        'success' => true,
                        'message' => "Fight was set to CANCEL",
                    ]);
                }

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failure to update record',

                ]);

            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'logs' => $e->getMessage(),
                'message' => 'Failure to update record',

            ]);

        }

    }
    public function replenishment_list(Request $request)
    {
        $params = (array) $request->all();
        $start = !empty($params['from']) ? $params['from'] : null;
        $end = !empty($params['to']) ? $params['to'] : null;

        $daterange = ($start != null && $end != null) ? [$start, $end] : false;
        $event = Events::getFirstActiveAndOpenEvent();
        if ($event && $daterange == false) {
            $query = DB::table('transactions')->join('users', 'users.id', '=', 'transactions.teller_id')
                ->select('users.name', 'transactions.*')
                ->where('event_id', $event->id)
                ->where('type', Transactions::REPLENISH)->get();
            return response()->json([
                'success' => true,
                'data' => $query,
            ]);
        } else {
            $query = DB::table('transactions')->join('users', 'users.id', '=', 'transactions.teller_id')
                ->select('users.name', 'transactions.*')
                ->where('type', Transactions::REPLENISH)
                ->when($daterange, function ($query, $daterange) {
                    $query->where(function ($query) use ($daterange) {
                        $query->whereBetween('transactions.created_at', [$daterange[0], $daterange[1]]);
                    });
                })->get();
            return response()->json([
                'success' => true,
                'data' => $query,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'No Recordf Found',
        ]);

    }
    public function replenish_cash(Request $request)
    {
        $params = (array) $request->all();

        $user = auth()->user();
        try {
            $event = Events::getFirstActiveAndOpenEvent();

            if ($event) {
                $transactions = [
                    'event_id' => $event->id,
                    'amount' => $params['amount'],
                    'fight_id' => 0,
                    'created_by' => $user->name,
                    'teller_id' => $params['id'],
                    'type' => Transactions::REPLENISH,
                ];
                TicketServices::transactions($transactions);
                return response()->json([
                    'success' => true,
                    'message' => 'Replenishment was made successfully',
                ]);

            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to replenish cash. No Active Event found',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'logs' => $e->getMessage(),
                'message' => 'Failure to make replenishment',

            ]);

        }
    }
    public function updateticket(Request $request)
    {
        try {
            $ticket = Tickets::where('ticket_no', '=', $request->ticket_no)->where('is_claimed', '=', Tickets::NOT_CLAIM)->first();
            if ($ticket) {
                $payout = PayoutServices::payout($request->ticket_no);
                if ($payout > 0) {
                    $updateTicket = Tickets::findOrFail($ticket->id);
                    if ($updateTicket) {
                        $user = auth()->user();
                        $params = (array) $request->all();

                        $updateTicket->is_claimed = Tickets::CLAIMED;
                        $updateTicket->claimed_on = date("Y-m-d H:i:s");
                        $updateTicket->amount_claimed = $payout;
                        $updateTicket->released_by = $user->id;
                        $updateTicket->save();
                        $transactions = [
                            'event_id' => $ticket->event_id,
                            'fight_id' => $ticket->fight_id,
                            'amount' => $payout,
                            'ticket_no' => $ticket->ticket_no,
                            'created_by' => $user->name,
                            'teller_id' => $user->id,
                            'type' => Transactions::CREDIT,
                        ];

                        TicketServices::transactions($transactions);
                        return response()->json([
                            'success' => true,
                            'message' => 'Winning was claimed successfully',

                        ]);
                    }

                }
                return response()->json([
                    'success' => false,
                    'message' => 'No Payout for this ticket',

                ]);

            }
            return response()->json([
                'success' => false,
                'message' => 'No Payout for this ticket',

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'logs' => $e->getMessage(),
                'message' => 'Failure to make payment',

            ]);

        }

    }
    public function updatefight(Request $request)
    {
        try {
            $fight = Fights::where('id', $request->id)
                ->where('status', Fights::OPEN)
                ->first();
            if ($fight) {

                if ($request->status == 5) {
                    $fight->bet_on_wala = Fights::CLOSE;
                    $fight->bet_on_meron = Fights::CLOSE;
                    $fight->start = Fights::START;
                    ComputePayoutJob::dispatch(['fight_no' => $request->id]);
                }
                if (isset($request->wala)) {
                    $fight->bet_on_wala = $request->current_value == Fights::CLOSE ? Fights::OPEN : Fights::CLOSE;
                }
                if (isset($request->meron)) {
                    $fight->bet_on_meron = $request->current_value == Fights::CLOSE ? Fights::OPEN : Fights::CLOSE;
                }

                $fight->save();

            }
            return response()->json([
                'success' => true,

                'message' => "Fight was updated successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'logs' => $e->getMessage(),
                'message' => 'Failure to update record',

            ]);
        }
    }

    public function getTicket(Request $request)
    {
        try {
            $ticket = Tickets::with([
                'fight',
                'event',
                'users' => function ($query) {
                    $query->select('id', 'name', 'email');
                }])

                ->where('ticket_no', '=', $request->ticket_no)
                ->first();
            if ($ticket) {
                $payout = PayoutServices::payout($ticket->ticket_no);
                $ticket['payout'] = $payout;

                return response()->json([
                    'success' => true,
                    'data' => $ticket,

                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Ticket Not Found',

            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failure to get record',

            ]);

        }

    }
    public function addticket(Request $request)
    {

        $data = [];
        $user = auth()->user();
        try {
            $event = Events::getFirstActiveAndOpenEvent();
            $fight = Fights::where('event_id', '=', $event->id)
                ->where('status', '=', Fights::OPEN)

                ->first();

            if ($event && $fight) {
                if ($fight->bet_on_meron == Fights::CLOSE && $request->team == Fights::MERON) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Betting on MERON is close',
                    ]);
                }
                if ($fight->bet_on_wala == Fights::CLOSE && $request->team == Fights::WALA) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Betting on WALA is close',
                    ]);
                }

                $ticket = Tickets::create([
                    'event_id' => $event->id,
                    'fight_id' => $fight->id,
                    'amount_bet' => $request->bet_amount,
                    'team' => $request->team,
                    'amount_claimed' => 0,
                    'is_claimed' => 0,
                    'created_by' => $user->id,
                ]);

                if ($ticket) {
                    $transactions = [
                        'event_id' => $event->id,
                        'fight_id' => $fight->id,
                        'amount' => $request->bet_amount,
                        'ticket_no' => sprintf("%06d", $ticket->id),
                        'created_by' => $user->name,
                        'teller_id' => $user->id,
                        'type' => Transactions::DEBIT,
                    ];
                    // Update the record with new values
                    $ticket->update([
                        'ticket_no' => sprintf("%06d", $ticket->id),
                    ]);
                    TicketServices::transactions($transactions);

                }

                // $user = User::where(['id' => $params['user_id']])->first();

                $data = $ticket;
                if ($user) {
                    $data['teller'] = $user->name;
                }
                $data['team'] = ($request->team == 1) ? 'MERON' : 'WALA';
                $data['event_name'] = $event->description;
                $data['event_no'] = $event->event_no;
                $data['fight_no'] = $fight->fight_no;
                return response()->json([
                    'success' => true,
                    'ticket' => $data,

                    'message' => 'Bet was saved successfully',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),

            ]);

        }

    }

}
