<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\UsersTiming;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Validator;
use Auth;

class TicketController extends Controller
{
    public function raiseTicket(Request $request)
    {
        
        $rules = [
    		'title' => 'required',
            'description' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        $userObj = $request->user();
        $ticketObj = new Ticket();
        $ticketObj->user_id = $userObj->id;
        $ticketObj->title = $request->title;
        $ticketObj->description = $request->description;
        $ticketObj->status = Ticket::IN_PROGRESS;
        if($ticketObj->save())
        return returnSuccessResponse("Ticked raised successfully",Ticket::getTricketList());
    }

    public function ticketList(Request $request)
    {
        $perPageRecords = !empty($request->query('per_page_record')) ? $request->query('per_page_record') : 10;
        $query = Ticket::with('userInfo');
        if($request->user()->role == User::ROLE_ADMIN){
        $query->where('status',Ticket::IN_PROGRESS)->orderBy('id','desc');
        }else{
            $query->where('user_id',$request->user()->id)->where('status',Ticket::IN_PROGRESS)->orderBy('id','desc');
        }
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($subquery) use ($search) {
                $subquery->where('title', 'like', '%' . $search . '%')
                         ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        $paginate = $query->paginate($perPageRecords);
        return returnSuccessResponse('Users Tickets',$paginate);
    }
    public function changeTicketStatus(Request $request)
    {
        $userObj = $request->user();
        $ticketObj = Ticket::find($request->ticket_id);
        if(!$ticketObj)
        return returnNotFoundResponse("Ticket not found");

        if($ticketObj->status !== Ticket::IN_PROGRESS)
        return returnValidationErrorResponse("Alrady updated");
    
        $status = Ticket::COMPLETED_BY_EMPLOYEE;
        if($userObj->role == User::ROLE_ADMIN){
            $status = Ticket::COMPLETED_BY_ADMIN;
        }
        $ticketObj->status = $status;
        if($ticketObj->save())
        return returnSuccessResponse("Ticket status changed successfully",Ticket::getTricketList());
    }
}
