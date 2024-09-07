<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;
 
use App\Http\Traits\FormatTraits;
use App\Models\Events;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController  extends BaseController
{
    protected $electionservices;
    use FormatTraits;

    

    public function list_events(Request $request)
    {
        

        $Event = Events::join('users', 'tbl_events.created_by', '=', 'users.id')
            ->select('tbl_events.*', 'users.name')
            ->where('tbl_events.isDelete', '=', 0)
            ->orderBy('tbl_events.id', 'DESC')

            ->get();

        return response()->json([
            
            'data' => $Event,

        ]);

    }
    public function update_event(Request $request, $id)
    {

        try {
            $Event = Events::findOrFail($id);
            if ($Event) {
                $user = auth()->user();
                $params = (array) $request->all();
                $Event->description = $request->description;
                $Event->isOpen = $request->isOpen;

                $Event->updated_at = date("Y-m-d H:i:s");
                $Event->updated_by = $user->id;
                $Event->save();
                return response()->json([
                    'success' => true,

                    'message' => "Successfully updated Event's Details",
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failure to update record',

                ]);

            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failure to update record',

            ]);

        }

    }
    public function delete_election(Request $request, $id)
    {
        try {
            $Event = Events::findOrFail($id);
            if ($Event) {
                $params = (array) $request->all();
                $Event->isDelete = 1;
                $Event->updated_at = date("Y-m-d H:i:s");
                $Event->deleted_by = $params['user_id'];
                $Event->save();
                return response()->json([
                    'success' => true,
                    'message' => "Event was deleted successfully",
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failure to delete record',

                ]);

            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failure to delete record',

            ]);

        }

    }
    public function create_events(Request $request)
    {
        try {
            $user = auth()->user();
            $params = (array) $request->all();
            $results = Events::select( DB::raw('COUNT(id) event_no'))
          
            ->first();
            $event_no="000001";
             if($results->event_no>=1){
                $event_no = sprintf("%06d", $results->event_no+1);
             }
             $event_id=date("Y").''.$event_no;
            Events::where('isActive', 1)
                ->update(['isOpen' => 0, 'isActive' => 0]);

            Events::create([
                'id'=>$event_id,
                'description' => $request->description,
                'event_date' => $request->event_date,
                'isActive' => $request->isActive,
                'event_no' => $event_no,
                'created_by' =>   $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully created  event',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                 'error'=>$e->getMessage(),
                'message' => 'Failure to create  event',

            ]);

        }

    }

}
