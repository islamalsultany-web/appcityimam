<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External system URLs (hosted separately)
    |--------------------------------------------------------------------------
    */

    'hr_url' => env('PORTAL_HR_URL', 'https://hr.cityimam.com/'),

    'finance_url' => env('PORTAL_FINANCE_URL', 'https://mony.cityimam.com/'),

    'assets_url' => env('PORTAL_ASSETS_URL', 'https://assetcity.cityimam.com/'),

    /*
    |--------------------------------------------------------------------------
    | Inquiry system entry (this Laravel app)
    |--------------------------------------------------------------------------
    */

    'inquiry_route' => env('PORTAL_INQUIRY_ROUTE', 'login.form'),

];
