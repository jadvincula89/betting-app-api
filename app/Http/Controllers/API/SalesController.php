<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\FormatTraits;
use App\Models\Events;
use App\Models\Tickets;
use App\Repositories\SalesRepository;
use App\Services\PayoutServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{

    use FormatTraits;

    protected $payoutServices;
    protected $salesRepositories;
    public function __construct(PayoutServices $payoutServices, SalesRepository $salesRepositories)
    {
        $this->payoutServices = $payoutServices;
        $this->salesRepositories = $salesRepositories;
    }
    public function index(Request $request)
    {
        try {
            $request = $request->query();

            $data = $this->payoutServices->sales_summary($request);
            return response()->json([
                'success' => true,
                'data' => $data,

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e,
                'message' => $e->getMessage(),

            ]);

        }

    }
    public function tickets(Request $request)
    {
        try {
            $event = Events::getFirstActiveAndOpenEvent();
            $is_claimed = !empty($request->query('is_claimed')) ? $request->query('is_claimed') : null;
            $is_claimed = intval($is_claimed) == 1 ? Tickets::CLAIMED : Tickets::NOT_CLAIM;

            if ($event) {

                $subquery = DB::table('tickets as t')
                    ->join('fights as f', 't.fight_id', '=', 'f.id')
                    ->join('tbl_events as e', 'f.event_id', '=', 'e.id')
                    ->select('t.*', 'f.fight_no', 'e.event_no')
                    ->where('t.event_id', $event->id)
                    ->whereColumn('t.winner', 'f.winner')
                    ->where('t.is_claimed', $is_claimed);

                $tickets = DB::table(DB::raw("({$subquery->toSql()}) as t"))
                    ->mergeBindings($subquery)
                    ->whereColumn('t.team', '=', 't.winner')
                    ->orderBy('t.id', 'desc')
                    ->get();
                return response()->json([
                    'success' => true,
                    'data' => $tickets,

                ]);

            }
            return response()->json([
                'success' => false,
                'message' => 'No Record Found',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'logs' => $e->getMessage(),
                'message' => 'Failure to make replenishment',

            ]);

        }
    }
    public function teller_summary(Request $request)
    {
        try {
            $params = $request->all();

            if (isset($params['from'])) {

                $event = DB::table('tbl_events as e')
                    ->leftJoin('fights as f', 'e.id', '=', 'f.event_id')
                    ->select('e.id', 'e.description', 'e.event_date', DB::raw('COUNT(f.fight_no) as fight'))
                    ->where('e.event_date', $params['from'])
                    ->groupBy('e.id', 'e.description', 'e.event_date')
                    ->first();
                if ($event) {

                    $data = $this->salesRepositories->teller_collection($event->id);
                    if ($data) {
                        return response()->json([
                            'success' => true,
                            'data' => $data,
                            'event' => $event,
                        ]);
                    }
                }
            } else {

                $event = DB::table('tbl_events as e')
                    ->leftJoin('fights as f', 'e.id', '=', 'f.event_id')
                    ->select('e.id', 'e.description', 'e.event_date', DB::raw('COUNT(f.fight_no) as fight'))
                    ->where('isActive', 1)
                    ->groupBy('e.id', 'e.description', 'e.event_date')
                    ->first();
                if ($event) {

                    $data = $this->salesRepositories->teller_collection($event->id);
                    if ($data) {
                        return response()->json([
                            'success' => true,
                            'data' => $data,
                            'event' => $event,
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => false,
                'data' => [],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e,
                'message' => $e->getMessage(),

            ]);

        }

    }

}
