<?php
namespace App\Http\Traits;

 
use Carbon\Carbon;

trait FormatTraits
{

    public function dateTimeFormat($timestamp)
    {
        return Carbon::parse($timestamp)->format('Y/m/d');
    }
    public function pagination($object)
    {
      return  
     [          'totalPages' =>ceil( $object->total()/$object->perPage()),
                'page' => $object->currentPage(),
                'per_page' => $object->perPage(),
                'overallTotal' => $object->total(),
            ];
    }
    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
 
    
 
}

