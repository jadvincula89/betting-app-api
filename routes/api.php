<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
  
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\FightController;
use App\Http\Controllers\API\SalesController;
   
Route::controller(RegisterController::class)->group(function(){

    Route::post('login', 'login');
    Route::post('register', [RegisterController::class, 'register']);
});
         
Route::middleware('auth:sanctum')->group( function () {
    Route::resource('products', ProductController::class);


    Route::get('users/list', [UserController::class, 'index']);

    Route::post('users', [UserController::class, 'add_user']);

    Route::put('users/{id}', [UserController::class, 'update_user']);
    Route::put('password/{id}', [UserController::class, 'update_password']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::post('user/details', [UserController::class, 'details']);
    Route::post('event', [EventController::class, 'create_events']);
    Route::get('event/list', [EventController::class, 'list_events']);
    Route::get('fight/list', [FightController::class, 'index']);
    Route::post('fight', [FightController::class, 'store']);
    Route::post('ticket', [FightController::class, 'addticket']);
    Route::post('claim_winning', [FightController::class, 'updateticket']);
    Route::post('get-ticket', [FightController::class, 'getTicket']);
    Route::put('winner/{id}', [FightController::class, 'updatewinner']);
    Route::put('fight/{id}', [FightController::class, 'updatefight']);
    Route::post('sales/summary', [SalesController::class, 'index']);
    Route::post('teller/summary', [SalesController::class, 'teller_summary']);
    Route::get('tellers', [SalesController::class, 'tellers']);
    Route::get('fight-details', [FightController::class, 'fight_details']);
    Route::put('event/{id}/update', [EventController::class, 'update_event']);
    Route::delete('event/{id}/delete', [EventController::class, 'delete_event']);

    Route::post('replenishment', [FightController::class, 'replenish_cash']);
    Route::post('replenishments', [FightController::class, 'replenishment_list']);
    Route::get('tickets', [SalesController::class, 'tickets']);
    
});