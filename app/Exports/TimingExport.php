<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UsersTiming;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimingExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $startDate;
    protected $userId;
    protected $endDate;

    public function __construct($startDate, $endDate,$userId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }
    public function collection()
    {
        $entries = UsersTiming::where('user_id',$this->userId)->whereBetween('server_time', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay(),
        ])->select('id', 'user_id','employee_id','date_time', 'status', 'server_time','total_hours')
        ->get();;
        $userdata = User::where('id',$this->userId)->first();
        if($userdata){
            $email = $userdata->email; 
            $name = $userdata->full_name;
        }else{
            $email = ""; 
            $name = "";
        }
        
        foreach ($entries as $entry) {
            $entry->status = UsersTiming::getStatusName($entry->status);
            $entry->user_id = $email;

            $keyIndex = array_search("user_id", array_keys($entry));

            $entry->name = $name;
        }
        return $entries;
    }

     public function headings(): array
    {
        // Define the header row
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
    
}
