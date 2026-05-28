<?php

return [

    'attachment' => [
        'max_kilobytes' => 5120,
        'mimes' => 'pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        'disk' => env('INQUIRY_ATTACHMENT_DISK', 'local'),
    ],

    'reviewer_isolation' => [
        'enabled' => env('REVIEWER_ISOLATION_ENABLED', true),
        'by_division' => env('REVIEWER_ISOLATION_BY_DIVISION', true),
        'by_type' => env('REVIEWER_ISOLATION_BY_TYPE', true),
    ],

];
