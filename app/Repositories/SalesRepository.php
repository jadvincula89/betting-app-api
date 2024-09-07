<?php
namespace App\Repositories;

use App\Models\Fights;
use App\Models\Tickets;
use Illuminate\Support\Facades\DB;

class SalesRepository
{

    public function  teller_collection($event_id,$fight_id=0)
    {
        $fight_query=$fight_id>0 ? " AND t.fight_id=$fight_id": "";
       
        $query = "
 SELECT t1.*,t2.* FROM ( 
SELECT 
        u.id,u.role,u.name, t.event_id ,
 
        SUM(CASE WHEN t.winner = t.team THEN t.shares ELSE 0 END) as shares ,
		SUM(CASE WHEN t.is_claimed = 0 and t.winner = t.team  THEN t.amount_claimed ELSE 0 END) as unclaimed 
		 
    FROM 
     users u
 
     LEFT JOIN tickets  t
     on u.id=t.created_by  
     AND
      t.event_id = ?
	
       ".$fight_query."
    GROUP BY   u.id,u.name,u.role, t.event_id) as t1,
 (SELECT 
        u.id,
         SUM(CASE WHEN t.type = 1 THEN t.amount ELSE 0 END) AS debit,
       SUM(CASE WHEN t.type = 2 THEN t.amount ELSE 0 END) AS credit,
         SUM(CASE WHEN t.type = 3 THEN t.amount ELSE 0 END) AS replenishment
    FROM 
     users u
    LEFT JOIN transactions t
  ON u.id=t.teller_id   
     AND t.event_id = ?  ".$fight_query."
     GROUP BY u.id) as t2
     where t1.id =t2.id 
";

$results = DB::select($query, [$event_id,$event_id]);
return $results;
    }
    public  function summary($request)
    {
         $start=!empty($request['from']) ?  $request['from'] :  null ;
         $end  =!empty($request['end'])  ?  $request['end'] :  null ;
         $keyword  =!empty($request['keyword'])  ?  $request['keyword'] :  null ;
        $daterange=($start !=null && $end !=null) ? [ $start,$end] : false;
        $subquery = DB::table('tickets')
    ->select('fight_id',
        DB::raw('SUM(IF(team = 1, amount_bet, 0)) as meron'),
        DB::raw('SUM(IF(team = 2, amount_bet, 0)) as wala'),
        DB::raw('SUM(IF(team = 1 AND is_claimed = 0, amount_claimed, 0)) as unclaimed_meron'),
        DB::raw('SUM(IF(team = 2 AND is_claimed = 0, amount_claimed, 0)) as unclaimed_wala'),
        DB::raw('SUM(IF(team = 1, amount_claimed, 0)) as payout_meron'),
        DB::raw('SUM(IF(team = 2, amount_claimed, 0)) as payout_wala'),
        DB::raw('SUM(IF(is_claimed = 0, amount_bet, 0)) as unclaimed_bet'),
        DB::raw('SUM(IF(team = 2, shares, 0)) as shares_wala'),
        DB::raw('SUM(IF(team = 1, shares, 0)) as shares_meron'),
    )
    ->groupBy('fight_id');

     $query = DB::table('fights as f')
    ->join('tbl_events as e', 'e.id', '=', 'f.event_id')
    ->joinSub($subquery, 'b', 'b.fight_id', '=', 'f.id')
    ->select(
        'e.id',
        'e.created_at',
        'f.fight_no',
        'e.event_no',
        'f.bet_percentage',
        'f.status',
        'f.winner',
        'b.meron',
        'b.wala',
        'b.unclaimed_meron',
        'b.unclaimed_wala',
        'b.unclaimed_bet',
        'b.payout_meron',
        'b.payout_wala',
        DB::raw('CASE WHEN f.winner = 1 THEN b.shares_meron WHEN f.winner = 2 THEN shares_wala ELSE 0 END as share'),
       
     )
    ->when($daterange, function ($query,$daterange) {
          $query->where(function ($query) use ($daterange) {
          $query->whereBetween('e.event_date', [$daterange[0],$daterange[1]]);
          });   
    })
        ->when($keyword, function ($query,$keyword) {
          $query->where(function ($query) use ($keyword) {
         $query->where('e.description', 'LIKE', "%{$keyword}%")
              ->orWhere('e.event_no', 'LIKE', "%{$keyword}%");
          });   
    })

            ->get();

            return $query;
           
    }
  

}
