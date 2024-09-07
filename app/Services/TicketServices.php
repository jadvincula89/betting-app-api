<?php
namespace App\Services;

use App\Models\Transactions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TicketServices
{
     
    public static function transactions($request)
    {
        return Transactions::create($request);
    }
    public static function quickRandom($length = 16)
    {
        $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    } 
 

    public function getrandomString($n = 10)
    {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

}
