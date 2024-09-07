<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;


class RegisterController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
           
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $auth = auth()->user();
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['fullname'] = $input['fullname'];
        $input['email'] = $input['email'];
        $input['role'] =  $input['role'];
        $input['created_by'] =   $auth->id;
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User register successfully.');
    }
    public function login(Request $request): JsonResponse
    {
        if(Auth::attempt(['email' => $request->username, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
            $success['name'] =  $user->name;
            $success['fullname'] =  $user->fullname;
            $success['role'] =  $user->role;
            $success['status'] =  $user->status;
          // if( $user->status==1){
            return $this->sendResponse($success, 'User login successfully.');
         //  }
           //else {
           // return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
          // }
           
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }
 
   
}
