<?php

namespace App\Exports;

use App\Models\AppUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppUsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return AppUser::query()->orderBy('id')->get();
    }

    public function headings(): array
    {
        return [
            'username',
            'employee_number',
            'badge_number',
            'division',
            'unit',
            'role',
            'created_at',
        ];
    }

    public function map($user): array
    {
        return [
            $user->username,
            $user->employee_number,
            $user->badge_number,
            $user->division,
            $user->unit,
            $user->role,
            optional($user->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
