<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\UsersTiming;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
class EmployeeController extends Controller
{
    public function login(Request $request)
    {
    	$rules = [
    		'email' => 'required',
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        try{
        $inputArr = $request->all();
        DB::beginTransaction();
        $userObj = User::where('email', $inputArr['email'])->first();
        if(empty($userObj))
            return returnNotFoundResponse('User Not found.');

        if($userObj->status == User::STATUS_INACTIVE)
            return returnErrorResponse("Your account is inactive please contact with admin.");
        if (!Auth::attempt(['email' => $inputArr['email'], 'password' => $inputArr['password']])) {
            return returnNotFoundResponse('Invalid credentials');
        }
        if($request->has("date_time") && !empty($request->date_time)){
            $dateTime = $request->date_time;
        }else{
            $dateTime = Carbon::now('UTC');
        }
        $userTimingObj = new  UsersTiming();
        $userTimingObj->user_id = $userObj->id;
        $userTimingObj->employee_id = $userObj->employee_id;
        $userTimingObj->status = UsersTiming::CLOCK_IN;
        $userTimingObj->date_time = $dateTime;
        $userTimingObj->server_time = Carbon::now('UTC');
        $userObj->save();
        $userTimingObj->save();
        DB::commit();
    } catch (\Throwable $th) {
        DB::rollBack();
    }
        $userObj->tokens()->delete();
        $authToken = $userObj->createToken('authToken')->plainTextToken;
        $returnArr = $userObj->jsonResponse();
        $returnArr['auth_token'] = $authToken;

        return returnSuccessResponse('Employee logged in successfully', $returnArr);
    }

    public function clockInClockOut(Request $request)
    {
        $rules = [
    		'status' => 'required',
            'date_time' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        $userObj = $request->user();
        $userTimingObj = new UsersTiming();
        $userTimingObj->user_id = $userObj->id;
        $userTimingObj->employee_id = $userObj->employee_id;
        $userTimingObj->status = $request->status;
        $userTimingObj->date_time = $request->date_time;
        $userTimingObj->server_time = Carbon::now('UTC');
        if($userTimingObj->save())
        return returnSuccessResponse('Clocked In', $userTimingObj->JsonResponseOfClockIns());
    }
    public function getHistory(Request $request)
    {
        $userId = $request->user_id;
        $totalHours = 0;
        if(empty($userId)){
            $userId = auth()->user()->id;
        }
        $timings = UsersTiming::where('user_id',$userId)->with('user')->get();
        // foreach($timings as $timing){
        //     $totalHours += $timing->total_hours;
        //     }
        // $startDateTime = $request->start_date_time;
        // $endDateTime = $request->end_date_time;
        // if(!empty($startDateTime) && !empty($endDateTime)){
        //     $timings = UsersTiming::where('user_id', $request->user_id)
        //     ->whereBetween('date_time', [$startDateTime, $endDateTime])
        //     ->with('user')
        //     ->get();
        //     foreach($timings as $timing){
        //     $totalHours += $timing->total_hours;
        //     }
        // }
        // $timings->setAttribute('total_hours', $totalHours);
        return returnSuccessResponse('History',$timings);
      
    }
   
}
