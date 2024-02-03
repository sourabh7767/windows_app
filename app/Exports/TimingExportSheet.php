<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UsersTiming;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TimingExportSheet implements FromCollection, WithHeadings,WithTitle
{
    protected $startDate;
    protected $userId;
    protected $endDate;

    public function __construct($startDate, $endDate, $userId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }

    public function collection()
    {
        $entries = UsersTiming::where('user_id', $this->userId)
            ->whereBetween('server_time', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ])->select('id', 'user_id', 'employee_id', 'date_time', 'status', 'server_time', 'total_hours')
            ->get()->toArray();
        $userdata = User::where('id',$this->userId)->first();
        if($userdata){
            $email = $userdata->email; 
            $name = $userdata->full_name;
        }else{
            $email = ""; 
            $name = "";
        }
        foreach ($entries as &$entry) {
            $status = UsersTiming::getStatusName($entry["status"]);

            $entry = [
                'id' => $entry["id"],
                'name' => $name,
                'email' => $email,
                'employee_id' => $entry["employee_id"],
                'date_time' => $entry["date_time"],
                'status' => $status,
                'server_time' => $entry["server_time"],
                'total_hours' => $entry["total_hours"],
            ];
        }
         unset($entry);
        // foreach ($entries as $entry) {
        //     $entry->status = UsersTiming::getStatusName($entry->status);
        //     $entry->user_id = User::where('id', $entry->user_id)->first()->email;
        // }

        return $entries;
    }

    public function headings(): array
    {
        return [
            'id',
            'Name',
            'Email',
            'Employee Id',
            'Date Time',
            'status',
            'UTC Time',
            'Total hours',
        ];
    }

    public function title(): string
    {
        $user = User::find($this->userId);
        return  $user ? $user->full_name : 'User';
    }
}
