<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Pagination
  |--------------------------------------------------------------------------
  | Default per-page counts for each section. Update here, not in code.
  */
  'pagination' => [
    'payments' => 20,
    'residents' => 15,
    'users' => 20,
    'reports' => 25,
    'media' => 24,
  ],

  /*
  |--------------------------------------------------------------------------
  | Registration Limits
  |--------------------------------------------------------------------------
  | Maximum number of user accounts allowed per residential unit.
  */
  'max_accounts_per_unit' => 3,

  /*
  |--------------------------------------------------------------------------
  | Internal SPA API Key
  |--------------------------------------------------------------------------
  | Used to restrict /api/* endpoints to the server's own React frontend.
  | The SPA receives this key via a Blade-injected meta tag (see spa.blade.php)
  | and sends it as the X-Api-Key request header.
  |
  | Set CIVICORE_API_KEY in your .env — never commit the real key.
  */
  'api_key' => env('CIVICORE_API_KEY', ''),

];
