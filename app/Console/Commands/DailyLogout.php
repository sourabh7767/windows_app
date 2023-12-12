<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class DailyLogout extends Command
{
    protected $signature = 'daily:logout';
    protected $description = 'Log out all users at the end of the day.';

    public function handle()
    {
        // Log out all users
        $this->info('Logging out all users...');

        // Revoke all personal access tokens
        $userObj = User::where('role',User::ROLE_EMPLOYEE)->get();
        foreach ($userObj as $key => $value) {
            $value->tokens()->delete();
        }

        $this->info('All users have been logged out.');
    }
}
