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
        $lastActiveThreshold = Carbon::now('UTC')->subMinutes(15);
        $lastEntries = UsersTiming::selectRaw('MAX(id) as max_id, user_id')
            ->groupBy('user_id')
            // ->where('server_time', '<=', $lastActiveThreshold)
            ->get();
        $lastEntriesData = UsersTiming::whereIn('id', $lastEntries->pluck('max_id'))
        ->get();
        foreach ($lastEntriesData as $entry) {
            if($entry->status == UsersTiming::BREAK_IN && $entry->server_time < $lastActiveThreshold){
                $diff = Carbon::now('UTC')->diff($entry->server_time);
                $totalTime = $diff->format('%H:%I') . " hours";
                $message = "is on break from $totalTime";
                $details = [
                    'user_name' => $entry->user->full_name,
                    'user_email' => $entry->user->email,
                    'employee_id' => $entry->user->employee_id,
                    'body' => $message,
                    'subject' => "Regarding Lunch break reminder",
                    'status' => $entry->status
                ];
                try{
                    \Mail::to('sssingh70875@yopmail.com')->send(new SendNoActivityMailToAdmin($details));
                    $this->info('Break reminder emails sent successfully.');
                } catch (\Throwable $th) {
                        $this->info($th->getMessage());
                    }
            }else{
                $this->info('Nothing find this user for break in.');
            }
        }
    }
}
