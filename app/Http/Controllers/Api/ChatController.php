<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketChat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Validator;

class ChatController extends Controller
{
    public function sendMessage(Request $request){
        $rules = [
    		'ticket_id' => 'required',
            'message' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        $userObj = $request->user();
        $chatObj = new TicketChat();
        $chatObj->from_id = $userObj->id;
        if($userObj->role == User::ROLE_EMPLOYEE){
            $chatObj->to_id = User::ROLE_ADMIN;
        }else{
            $ticketObj = Ticket::find($request->ticket_id);
            if(!$ticketObj)
            return returnNotFoundResponse("Ticket not found");
            $chatObj->to_id = $ticketObj->user_id;
        }
        $chatObj->ticket_id = $request->ticket_id;
        $chatObj->message = $request->message;

        $query = TicketChat::where('ticket_id',$request->ticket_id);
        $allMessages = $query->paginate(20);
        if($chatObj->save())
        return returnSuccessResponse("Message sent", $allMessages);
    }

    public function getMessages(Request $request)
    {
        $perPageRecords = !empty($request->query('per_page_record')) ? $request->query('per_page_record') : 20;
       $query = TicketChat::where('ticket_id',$request->ticket_id);
       $paginate = $query->paginate($perPageRecords);

       return returnSuccessResponse("All messages", $paginate);
    }
}
