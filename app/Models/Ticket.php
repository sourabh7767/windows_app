<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    const IN_PROGRESS = 1;
    const COMPLETED_BY_EMPLOYEE = 2;
    const COMPLETED_BY_ADMIN = 3;

    public function getStatusName($value) {
        switch ($value) {
            case self::IN_PROGRESS:
                return 'In progress';
            case self::COMPLETED_BY_EMPLOYEE:
                return 'Completed by Employee';
            case self::COMPLETED_BY_ADMIN:
                return 'Completed by admin';
            default:
                return 'Unknown';
        }
    }

    public function JsonResponseForTickets(){

        $json['id'] = $this->id;
        $json['user_id'] = $this->user_id;
        $json['title'] = $this->title;
        $json['description'] = $this->description;
        $json['status'] = $this->status;
        $json['status_title'] = $this->getStatusName($this->status);
        $json['created_at'] = $this->created_at;
        $json['updated_at'] = $this->updated_at;
        return $json;
    }

    public static function getPaginateObj($paginate, $jsonObj = "jsonObj")
    {

        $items = $paginate->items();
        $json = [];
        foreach ($items as $item) {
            $json[] = $item->$jsonObj();
        }
        return [
            "list" => $json,
            "current_page" => $paginate->currentPage(),
            "next_page" => $paginate->nextPageUrl(),
            "last_page" => $paginate->lastPage(),
            "per_page" => $paginate->perPage(),
            "total" => $paginate->total(),
        ];
    }

    public function jsonObj()
    {
        $jsonData = $this->JsonResponseForTickets();
        return $jsonData;
    }
    
}
