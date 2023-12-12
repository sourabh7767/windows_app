<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/export', [EmployeeController::class, 'exportTimings'])->name('exportTimings');
Route::middleware('prevent-back-history')->group(function (){

    Route::get('/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        
        return Redirect::back()->with('success', 'All cache cleared successfully.');
    });

    Auth::routes();
    Route::middleware('auth')->group(function(){

        Route::get('/', 'HomeController@index')->name('user.home');
        Route::resource('users', 'UserController');
        Route::resource('role', 'RoleController');
        Route::post('createRole','RoleController@createRole')->name('createRole');
        Route::post('updateRole','RoleController@updateRole')->name('updateRole');
        Route::get('/user/changeStatus/{id}','UserController@changeStatus')->name('user.changeStatus');
        Route::get('user/profile','UserController@profile')->name('user.profile');
        Route::get('user/update-profile','UserController@showUpdateProfileForm')->name('user.updateProfile');
        Route::post('user/update-profile','UserController@updateProfile')->name('user.updateProfile.submit');
        Route::get('user/change-password','UserController@changePasswordView')->name('user.changePassword');
        Route::post('user/change-password','UserController@changePassword')->name('user.changePassword.submit');

        Route::resource('page', 'PageController');
        Route::post('upload', 'PageController@upload')->name('upload');

        

     

    });
});
