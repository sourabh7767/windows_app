<?php

namespace App\Console\Commands;

use App\Mail\SendNoActivityMailToAdmin;
use App\Models\UsersTiming;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendBreakReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:break-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email sent successfully';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $breakHistories = UsersTiming::whereDate('server_time', Carbon::now('UTC'))
        ->whereIn('status', [UsersTiming::BREAK_IN])
        ->where(function ($query) {
            $lastActiveThreshold = Carbon::now('UTC')->subMinutes(15);
            $query->whereIn('id', function ($subQuery) use ($lastActiveThreshold) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('users_timings')
                    ->where('status', UsersTiming::BREAK_IN)
                    ->groupBy('user_id');
            });
            // Additional condition to check if the selected break_in record is older than 15 minutes
            $query->where('server_time', '<=', $lastActiveThreshold);
        })
        ->with('user')
        ->orderBy('id', 'desc')
        ->get();
        foreach ($breakHistories as $breakHistory) {
            $diff = Carbon::now('UTC')->diff($breakHistory->server_time);
            $totalTime = $diff->format('%H:%I') . " hours";
            $message = "is on break from $totalTime";
            $details = [
                'user_name' => $breakHistory->user->full_name,
                'user_email' => $breakHistory->user->email,
                'employee_id' => $breakHistory->user->employee_id,
                'body' => $message,
                'subject' => "Regarding break reminder"
            ];
            try{
                \Mail::to(env("ADMIN_EMAIL"))->send(new SendNoActivityMailToAdmin($details));
            } catch (\Throwable $th) {
                    $this->info('Break reminder emails sent successfully.');
                }
        }
    }
}
