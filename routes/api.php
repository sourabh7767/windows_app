<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('v1')->namespace('Api')->group(function () {
   
	Route::prefix('admin')->name('admin.')->group(function () {
		Route::post('/adminLogin', 'UserController@adminLogin')->name('adminLogin');
		Route::middleware(['auth:sanctum'])->group(function () {
			Route::post('/createEmploye', 'UserController@createEmploye')->name('createEmploye');
			Route::post('/updateProfile', 'UserController@updateProfile')->name('updateProfile');
			Route::post('/deleteEmploye', 'UserController@deleteEmploye')->name('deleteEmploye');
			Route::get('/getEmployeWithSearch', 'UserController@getEmployeWithSearch')->name('getEmployeWithSearch');
		});
	});
    // Authentication Routes
    Route::prefix('user')->name('user.')->group(function () {
        Route::post('/login', 'EmployeeController@login')->name('login');

		Route::middleware(['auth:sanctum'])->group(function () {
			Route::post('/time/status/update', 'EmployeeController@clockInClockOut')->name('clockInClockOut');
			Route::post('/create/ticket', 'TicketController@raiseTicket')->name('raiseTicket');
			Route::get('/ticketList', 'TicketController@ticketList')->name('ticketList');
			Route::post('/changeTicketStatus', 'TicketController@changeTicketStatus')->name('changeTicketStatus');
			Route::post('/sendMessage', 'ChatController@sendMessage')->name('sendMessage');
			Route::get('/getMessages', 'ChatController@getMessages')->name('getMessages');
			Route::post('/get/history', 'EmployeeController@getHistory')->name('getHistory');
			Route::post('/no/activity', 'HomeController@noActivity')->name('noActivity');
			Route::get('/logout', 'HomeController@logout')->name('logout');
		
		});
    });


});