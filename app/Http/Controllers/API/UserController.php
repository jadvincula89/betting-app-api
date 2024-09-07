<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class UserController extends BaseController
{
  
    public function index(Request $request)
    {
        $perPage = $request->input('results', 10);
        if (!is_numeric($perPage)) {
            $perPage = 10;
        }

        $users = User::all();
      
        return response()->json([

            'data' => $users,

        ]);

    }
    public function update_password(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user) {
                $auth = auth()->user();
                $input = $request->all();
                $user->updated_by = $auth->id;
              //  $user->password = bcrypt($input['password']);
                $user->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Password was updated successfully',
    
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error_message'=>$e->getMessage(),
                'message' => 'Failure to update password',

            ]);

        }
    }
    public function destroy($id)
    {
        // Find the user by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Delete the user
        $user->delete();

        // Return a response
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
    public function update_user(Request $request, $id)
    {

        try {
            $user = User::findOrFail($id);
            if ($user) {
             
                $auth = auth()->user();
               
           
               
                $user->updated_at = date("Y-m-d H:i:s");
                $user->fullname = $request->fullname;
                $user->role = $request->role;
                $user->status = $request->status;
                $user->name = $request->user_name;
                $user->updated_by = $auth->id;
                $user->save();
                return response()->json([
                    'success' => true,

                    'message' => "Successfully updated User's Details",
                ]);

            }  

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error_message'=>$e->getMessage(),
                'message' => 'Failure to update record',

            ]);

        }

    }
    public function details(Request $request)
    {
        try {
            $user = auth()->user();
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' =>   $user ,

            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failure to load details',

            ]);

        }

    }
   
}
