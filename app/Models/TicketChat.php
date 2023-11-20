<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketChat extends Model
{
    use HasFactory;

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

     public function JsonResponseForTicketChat($flag = false){

        $json['id'] = $this->id;
        $json['from_id'] = $this->from_id;
        $json['to_id'] = $this->to_id;
        $json['ticket_id'] = $this->ticket_id;
        $json['message'] = $this->message;
        $json['created_at'] = $this->created_at;
        $json['updated_at'] = $this->updated_at;
        if($flag){
            $json['ticket'] = $this->ticket->JsonResponseForTickets();
        }
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
        $jsonData = $this->JsonResponseForTicketChat();
        return $jsonData;
    }
}
