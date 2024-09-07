<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
  
    const DEBIT=1;
    const CREDIT=2;
    const REPLENISH=3;
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'fight_id',
        'event_id',
        'amount',
        'created_by',
        'ticket_no',
        'type',
        'teller_id'
    ];

}
