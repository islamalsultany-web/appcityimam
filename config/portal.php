<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External system URLs (hosted separately)
    |--------------------------------------------------------------------------
    */

    'hr_url' => env('PORTAL_HR_URL', 'http://172.12.26.144:8090'),

    'finance_url' => env('PORTAL_FINANCE_URL', 'http://172.12.26.144:8890'),

    'assets_url' => env('PORTAL_ASSETS_URL', 'http://172.12.26.144:8020'),

    /*
    |--------------------------------------------------------------------------
    | Inquiry system entry (this Laravel app)
    |--------------------------------------------------------------------------
    */

    'inquiry_route' => env('PORTAL_INQUIRY_ROUTE', 'login.form'),

];
