<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function adminLogin(Request $request){
        $rules = [
            'password' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        $userObj = User::where('role', User::ROLE_ADMIN)->first();
        if ($userObj && password_verify($request->password, $userObj->password)) {
            $userObj->tokens()->delete();
            $authToken = $userObj->createToken('authToken')->plainTextToken;
            $returnArr = $userObj->jsonResponseAdmin();
            $returnArr['auth_token'] = $authToken;
            return returnSuccessResponse('Admin login',$returnArr);
        }
        return returnValidationErrorResponse("Incorect Password");
    }
    public function createEmploye(Request $request ,User $user){

        $rules = [
            'employee_id' =>'required',
    		'full_name'=>'required',
            'username' =>'required',
            'email' => 'required|email:rfc,dns,filter|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }

        $user = $user->fill($request->all());
        $user->role=User::ROLE_EMPLOYEE;
       if( $user->save()){
        return returnSuccessResponse('Employee created successfully',$user->jsonResponse());
       }
    }

    public function getEmployeWithSearch(Request $request)
    {
        $perPageRecords = !empty($request->query('per_page_record')) ? $request->query('per_page_record') : 20;
        $searchQuery = $request->input('search');

        $query = User::where('role', '!=', User::ROLE_ADMIN)
            ->where(function ($query) use ($searchQuery) {
                $query->orWhere('username', 'like', '%' . $searchQuery . '%')
                    ->orWhere('full_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $searchQuery . '%');
            });
        $paginate = $query->paginate($perPageRecords);

        return returnSuccessResponse("User list", $paginate);
    }
    public function updateProfile(Request $request){

        $rules = [
            'emp_id' =>'required',
    		'full_name'=>'required',
            'username' =>'required',
            'email' => [
                'required',
                'email:rfc,dns,filter',
                Rule::unique('users')->ignore($request->user_id, 'id')->whereNull('deleted_at')
            ],
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        // $userObj = $request->user();
        $userObj = User::find($request->user_id);
        if (!$userObj) {
            return returnValidationErrorResponse('User not found');
        }

        $userObj->full_name = $request->full_name;
        $userObj->employee_id = $request->emp_id;
        $userObj->username = $request->username;
        $userObj->email = $request->email;
        $userObj->password = $request->password;
        if(!$userObj->save()){
            return returnErrorResponse('Unable to save data');
        }

        $returnArr = $userObj->jsonResponse();
        return returnSuccessResponse('Profile updated successfully', $returnArr);
    }

    public function deleteEmploye(Request $request){

        $rules = [
            'user_id' =>'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            throw new HttpResponseException(returnValidationErrorResponse($errorMessages[0]));
        }
        $userObj = User::find($request->user_id);
        if (!$userObj) {
            return returnValidationErrorResponse('User not found');
        }
        if($userObj->delete()){
            $users = User::getAllUsersResponse();
            return returnSuccessResponse('Employee deleted successfully',$users);
        }
    }
   
}
