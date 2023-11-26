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
        return returnSuccessResponse('Employee created successfully', User::getAllUsersResponse());
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
            'user_id' => 'required',
            'emp_id' =>'sometimes',
    		'full_name'=>'sometimes',
            'username' =>'sometimes',
            'email' => [
                'sometimes',
                'email:rfc,dns,filter',
                Rule::unique('users')->ignore($request->user_id, 'id')->whereNull('deleted_at')
            ],
            'password' => 'sometimes',
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
        if(!empty($request->full_name)){
        $userObj->full_name = $request->full_name;
        }
        if(!empty($request->emp_id)){
        $userObj->employee_id = $request->emp_id;
        }
        if(!empty($request->username)){
        $userObj->username = $request->username;
        }
        if(!empty($request->email)){
        $userObj->email = $request->email;
        }
        if(!empty($request->password)){
        $userObj->password = $request->password;
        }
        if(!$userObj->save()){
            return returnErrorResponse('Unable to save data');
        }

        $returnArr = $userObj->jsonResponse();
        return returnSuccessResponse('Profile updated successfully', User::getAllUsersResponse());
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
            return returnSuccessResponse('Employee deleted successfully',User::getAllUsersResponse());
        }
    }
   
}
