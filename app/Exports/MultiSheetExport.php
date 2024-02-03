<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UsersTiming;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class MultiSheetExport implements  FromCollection, WithHeadings, WithMultipleSheets, WithTitle
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
            ->get();

        foreach ($entries as $entry) {
            $entry->status = UsersTiming::getStatusName($entry->status);
            $entry->user_id = User::where('id', $entry->user_id)->first()->email;
        }

        return $entries;
    }

    public function headings(): array
    {
        return [
            'id',
            'Email',
            'Employee Id',
            'Date Time',
            'status',
            'UTC Time',
            'Total hours',
        ];
    }

    public function sheets(): array
    {
        $users = User::where("role",User::ROLE_EMPLOYEE)->get();
        $sheets = [];
    
        foreach ($users as $user) {
            $hasRecords = UsersTiming::where('user_id', $user->id)
                ->whereBetween('server_time', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay(),
                ])->exists();
    
            if ($hasRecords) {
                $user = User::find($user->id);
                $sheets[] = new TimingExportSheet($this->startDate, $this->endDate, $user->id);
            } else {
                $sheets[] = new class ($user) implements FromCollection, WithHeadings,WithTitle {
                    private $user;
    
                    public function __construct($user)
                    {
                        $this->user = $user;
                    }
    
                    public function collection()
                    {
                        return collect([
                            [
                                'id' => "",
                                'Name' => "",
                                'Email' => "",
                                'Employee Id' => "",
                                'Date Time' => "",
                                'status' => "",
                                'UTC Time' => "",
                                'Total hours' => "",
                            ],
                        ]);
                    }
    
                    public function headings(): array
                    {
                        return [
                            'id',
                            "Name",
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
                        return $this->user->id ? $this->user->full_name : "";
                        // $user = User::find($this->user->id);
                        // return  $user ? $user->employee_id : 'User';
                    }
                };
            }
        }
    
        return $sheets;
    }
 
    public function title(): string
    {
        $user = User::find($this->userId);
        return  $user ? $user->employee_id : 'User';
    }
   
}

