<?php

namespace App\Http\Controllers\Api;

use App\Exports\MultiSheetExport;
use App\Exports\TimingExport;
use App\Http\Controllers\Controller;
use App\Models\MasterData;
use App\Models\Ticket;
use App\Models\UsersTiming;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
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
        $masterData = MasterData::get();
       
        $timings = UsersTiming::where('user_id', $userObj->id)
        ->where(function ($query) {
            $query->where('status', UsersTiming::BREAK_IN)
                ->orWhere('status', UsersTiming::BREAK_OUT);
        })
        ->orderBy('id', 'asc')
        ->get();
        $totalBreakCount = $timings->count();

        if ($totalBreakCount % 2 == 0) {
            $flag = true;
        } else {
            $flag = false;
        }
    
        $totalBreakDuration = 0;
        $lastEntry = 0;
        $breakInTime = null;
        
        foreach ($timings as $timing) {
            $lastEntry++;
            if ($timing->status == UsersTiming::BREAK_IN) {
                $breakInTime = Carbon::parse($timing->server_time);
            } elseif ($timing->status == UsersTiming::BREAK_OUT && $breakInTime !== null) {
                
                $breakOutTime = Carbon::parse($timing->server_time);
                $breakDuration = $breakOutTime->diffInMinutes($breakInTime);
                 
                $totalBreakDuration += $breakDuration;
                
                $breakInTime = null;
            }
            $breakInTimeIndex = $totalBreakCount -1;
            if ($lastEntry == $totalBreakCount) {
                if ($flag) {
                    $currentTime = Carbon::parse($timings[$breakInTimeIndex]->server_time);
                    $breakOutTime = Carbon::parse($timing->server_time);
                    $lastBreakDuration = $currentTime->diffInMinutes($breakOutTime);
                } else {
                    $currentTime = Carbon::now("UTC");
                    // $breakInTime = Carbon::parse($timing->server_time)->setTimezone('UTC');
                    // echo "<pre>";print_r($currentTime);
                    // echo "<pre>";print_r($breakInTime);die;
                    $lastBreakDuration = $currentTime->diffInMinutes($timing->server_time);
                }
                $totalBreakDuration += $lastBreakDuration;
            }
        }
    
        $isMeating = UsersTiming::where('user_id', $userObj->id)->whereDate('server_time',Carbon::now("UTC"))->where('status',UsersTiming::MEETING_IN)->first();
        if($isMeating){
            $meating = true;
        }else{
            $meating = false;
        }
        $returnArr['master_data'] = $masterData;
        $returnArr['total_break_time'] = $totalBreakDuration;
        $returnArr['is_meating_in'] = $meating;
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
        $rules = [
    		'start_date_time' => 'required',
            'end_date_time' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        // $userId = $request->user_id;
        // $totalHours = 0;
        // if(empty($userId)){
        //     $userId = auth()->user()->id;
        // }
        // $timings = UsersTiming::where('user_id',$userId)->with('user')->get();
        // return returnSuccessResponse('History',$timings);
      
        $userId = $request->user_id;
        $totalHours = 0;
        if(empty($userId)){
            $userId = auth()->user()->id;
        }
        $startDateTime = $request->start_date_time;
        $endDateTime = $request->end_date_time;

        $results = UsersTiming::where('user_id', $userId)
        ->whereBetween('date_time', [$startDateTime, $endDateTime])
        ->selectRaw('*, TIME_TO_SEC(total_hours) as total_seconds')
        ->orderBy("id","desc")
        ->paginate();
        $totalSeconds = $results->sum('total_seconds');
        $totalHours = gmdate("H:i:s", $totalSeconds);
        
        if(!empty($startDateTime) && !empty($endDateTime)){
            $endDateTime = date('Y-m-d H:i:s', strtotime($endDateTime . ' +1 day'));
            $results = UsersTiming::whereBetween('date_time', [$startDateTime, $endDateTime])
            ->where('user_id', $userId)
            ->selectRaw('*, TIME_TO_SEC(total_hours) as total_seconds')
            ->orderBy("id","desc")
            ->paginate();
            $totalSeconds = $results->sum('total_seconds');
            $totalHours = gmdate("H:i:s", $totalSeconds);
        }
        $data = ['main_data' => $results,'total_hours' => $totalHours ];
        return returnSuccessResponse('History',$data);
    }
    public function exportTimings(Request $request)
    {
        $startDate = $request->input('start_date_time');
        $endDate = $request->input('end_date_time');
        $userId = $request->input('user_id');
        return \Maatwebsite\Excel\Facades\Excel::download(new TimingExport($startDate, $endDate,$userId), 'timing_export.xlsx');
    }
    public function exportTimingsMultiSheets(Request $request)
    {
        $startDate = $request->input('start_date_time');
        $endDate = $request->input('end_date_time');
        $userId = $request->input('user_id');
        return \Maatwebsite\Excel\Facades\Excel::download(new MultiSheetExport($startDate, $endDate,$userId), 'timing_multi_export.xlsx');
    }
   
}
