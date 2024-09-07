<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    use HasFactory;
    const NOT_CLAIM = 0;
    const CLAIMED = 1;
    
    protected $table = 'tickets';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $fillable = [
        'fight_id',
        'event_id',
        'created_by',
        'amount_bet',
        'team',
        'created_at',
        'is_claimed',
        'claimed_amount',
        'shares',
        'claimed_on',
        'released_by',
        'ticket_no',
        'winner'
    ];
    public function fight()
    {
        return $this->belongsTo(Fights::class,'fight_id','id');
   
    }
    public function event()
    {
        return $this->belongsTo(Events::class,'event_id','id');
   
    }
    public function users()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
    public function release()
    {
        return $this->belongsTo(User::class,'released_by','id');
    }
}
