<?php

namespace App\Console\Commands;

use App\Mail\SendNoActivityMailToAdmin;
use App\Models\UsersTiming;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendLunchBreakReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:lunch-break-reminder';

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
        $lunchBreakHistories = UsersTiming::whereDate('server_time', Carbon::now('UTC'))
        ->whereIn('status', [UsersTiming::LUNCH_IN])
        ->where(function ($query) {
            $lastActiveThreshold = Carbon::now('UTC')->subMinutes(45);
            $query->whereIn('id', function ($subQuery) use ($lastActiveThreshold) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('users_timings')
                    ->where('status', UsersTiming::LUNCH_IN)
                    ->groupBy('user_id');
            });
            $query->where('server_time', '<=', $lastActiveThreshold);
        })
        ->with('user')
        ->orderBy('id', 'desc')
        ->get();
        foreach ($lunchBreakHistories as $lunchBreakHistory) {
            $diff = Carbon::now('UTC')->diff($lunchBreakHistory->server_time);
            $totalTime = $diff->format('%H:%I') . " hours";
            $message = "is on Lunch from $totalTime";
            $details = [
                'user_name' => $lunchBreakHistory->user->full_name,
                'user_email' => $lunchBreakHistory->user->email,
                'employee_id' => $lunchBreakHistory->user->employee_id,
                'body' => $message,
                'subject' => "Regarding Lunch break reminder"
            ];
            try{
                \Mail::to('sssingh70875@gmail.com')->send(new SendNoActivityMailToAdmin($details));
            } catch (\Throwable $th) {
                    $this->info('Break reminder emails sent successfully.');
                }
        }
    }
}
