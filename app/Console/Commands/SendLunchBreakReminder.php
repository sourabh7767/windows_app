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
        $lastActiveThreshold = Carbon::now('UTC')->subMinutes(45);
        $lastEntries = UsersTiming::selectRaw('MAX(id) as max_id, user_id')
        ->where('server_time', '<=', $lastActiveThreshold)
            ->groupBy('user_id')
            ->get();
            
        $lastEntriesData = UsersTiming::whereIn('id', $lastEntries->pluck('max_id')) // Add this condition
        ->get();
     
        foreach ($lastEntriesData as $entry) {
            if($entry->status == UsersTiming::LUNCH_IN){
                $diff = Carbon::now('UTC')->diff($entry->server_time);
                $totalTime = $diff->format('%H:%I') . " hours";
                $message = "is on Lunch from $totalTime";
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
                    $this->info('Lunch reminder emails sent successfully.');
                } catch (\Throwable $th) {
                        $this->info($th->getMessage());
                    }
            }else{
                $this->info('Nothing find this user for lunch in.');
            }
        }
    }
}
