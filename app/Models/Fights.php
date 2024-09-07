<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fights extends Model
{
    use HasFactory;
    const MERON = 1;
    const WALA = 2;
    const TABLA = 3;
    const CANCEL = 0;
    const OPEN=0;
    const CLOSE=1;
    const START=1;
    const STOP=0;
    protected $table = 'fights';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'fight_no',
        'event_id',
        'created_by',
        'total_meron_bet',
        'total_wala_bet',
        'winner',
        'status',
        'bet_percentage',
        'date_created',
        'bet_on_meron',
        'bet_on_wala',
        'updated_by',
        'start'
        
    ];

}
