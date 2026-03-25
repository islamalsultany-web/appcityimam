<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AppUsersTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'اسم المستخدم',
            'كلمة المرور',
            'تأكيد كلمة المرور',
            'الرقم الوظيفي',
            'رقم الباج',
            'الشعبة',
            'الوحدة',
            'الدور',
        ];
    }

    public function array(): array
    {
        return [
            ['', '', '', '', '', '', '', 'asker'],
        ];
    }
}
