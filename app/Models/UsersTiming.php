<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersTiming extends Model
{
    use HasFactory;
    const CLOCK_IN = 1;
    const CLOCK_OUT = 2;
    const LUNCH_IN = 3;
    const LUNCH_OUT = 4;
    const MEETING_IN = 5;
    const MEETING_OUT = 6;
    const BREAK_IN = 7;
    const BREAK_OUT = 8;
    public function getStatusName($value) {
        switch ($value) {
            case self::CLOCK_IN:
                return 'Clock In';
            case self::CLOCK_OUT:
                return 'Clock Out';
            case self::LUNCH_IN:
                return 'Lunch In';
            case self::LUNCH_OUT:
                return 'Lunch Out';
            case self::MEETING_IN:
                return 'Meeting In';
            case self::MEETING_OUT:
                return 'Meeting Out';
            case self::BREAK_IN:
                return 'Break In';
            case self::BREAK_OUT:
                return 'Break Out';
            default:
                return 'Unknown';
        }
    }
    
    public function JsonResponseOfClockIns(){

        $json['id'] = $this->id;
        $json['user_id'] = $this->user_id;
        $json['employee_id'] = $this->employee_id;
        $json['status'] = $this->status;
        $json['status_title'] = $this->getStatusName($this->status);
        $json['date_time'] = $this->date_time;
        $json['last_active'] = 1;//$this->last_active;
        $json['created_at'] = $this->created_at;
        $json['updated_at'] = $this->updated_at;
        return $json;
    }
}
