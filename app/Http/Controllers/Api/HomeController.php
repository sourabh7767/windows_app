<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendNoActivityMailToAdmin;
use App\Models\User;
use App\Models\UsersTiming;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\ContactUs;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;
use Validator;

class HomeController extends Controller
{
    public function logout(Request $request)
    {
        $userObj = $request->user();
        if (!$userObj) {
            return returnValidationErrorResponse('You are not authorized');
        }
        if($request->has("date_time") && !empty($request->date_time)){
            $dateTime = $request->date_time;
        }else{
            $dateTime = Carbon::now('UTC');
        }
        if($userObj->role == User::ROLE_EMPLOYEE){
            $userTimingObj = new  UsersTiming();
            $userTimingObj->user_id = $userObj->id;
            $userTimingObj->employee_id = $userObj->employee_id;
            $userTimingObj->status = UsersTiming::CLOCK_OUT;
            $userTimingObj->date_time = $dateTime;
            $userTimingObj->server_time = Carbon::now('UTC');
            $userTimingObj->save();
            if($request->has("date_time") && !empty($request->date_time)){
                $getClockIn = UsersTiming::where('user_id',$userObj->id)->whereDate('server_time',Carbon::now('UTC'))->where('status',UsersTiming::CLOCK_IN)->orderBy('id','desc')->first();
                $getClockOut =UsersTiming::where('user_id',$userObj->id)->whereDate('server_time',Carbon::now('UTC'))->where('status',UsersTiming::CLOCK_OUT)->orderBy('id','desc')->first();
                
                $clockInServerTime = Carbon::parse($getClockIn->server_time);
                $clockOutServerTime = Carbon::parse($getClockOut->server_time);
                $diff = $clockInServerTime->diff($clockOutServerTime);
                $timeToBeUpdate = UsersTiming::find($userTimingObj->id);
                $timeToBeUpdate->update([
                    'total_hours' =>  $diff->format('%H:%I:%S')
                ]);
            }
        }
        // $$timeToBeUpdate->total_hours = $diff; //->format('');
        $userObj->tokens()->delete();
        return returnSuccessResponse('User logged out successfully',@$timeToBeUpdate);
    }
    public function getPage(Request $request){

       $page_type = $request->page_type;

       if(empty($page_type))
          return returnErrorResponse('Please send page type.');

      $page = Page::where('page_type',$page_type)->first();

   return returnSuccessResponse('Data sent successfully',$page);

}

public function contactUs(Request $request,ContactUs $contactUs){

	$rules = [
            'full_name' => 'required',
            'email' => 'required',
            'category' => 'required|integer|min:1|max:3',
            'description' => 'required'

        ];
         $inputArr = $request->all();
        $validator = Validator::make($inputArr, $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }

        $contactUs = $contactUs->fill($request->all());
        if($contactUs->save())
        	return returnSuccessResponse('Message sent successfully',$contactUs);

        return returnErrorResponse("Something went wrong.");

}
public function noActivity(Request $request)
{
    $userObj = $request->user();
        $details = [
            'user_name' => $userObj->full_name,
            'user_email' => $userObj->email,
            'employee_id' => $userObj->employee_id,
            'body' => "is inactive more then 15 min",
            'subject' => "Regarding no activity of employee"
        ];
        // try{
            \Mail::to(env("ADMIN_EMAIL"))->send(new SendNoActivityMailToAdmin($details));
            // } catch (\Throwable $th) {
                // return returnErrorResponse("Unable to send mail");
            // }
    return returnSuccessResponse("Mail sent successfully");
}
}
