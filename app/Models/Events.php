<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;
    protected $table = 'tbl_events';
    protected $fillable = [
        'id',
        'description',
        'isActive',
        'event_no',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'isOpen',
        'isDelete',
        'event_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public static function getFirstActiveAndOpenEvent()
    {
        return self::where('isActive', 1)
                    ->where('isOpen', 1)
                    ->first();
    }
}


