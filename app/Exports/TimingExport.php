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
        ])->get();
        foreach ($entries as $entry) {
            $entry->status = UsersTiming::getStatusName($entry->status);
            $entry->user_id = User::where('id',$entry->user_id)->first()->email;
        }
        return $entries;
    }

     public function headings(): array
    {
        // Define the header row
        return [
            'id',
            'Email',
            'Employee Id',
            'Date Time',
            'status',
            'last Activity',
            'created At',
            'updated At',
            'Server Time',
        ];
    }
}
