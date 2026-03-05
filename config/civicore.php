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
  ],

  /*
  |--------------------------------------------------------------------------
  | Registration Limits
  |--------------------------------------------------------------------------
  | Maximum number of user accounts allowed per residential unit.
  */
  'max_accounts_per_unit' => 3,

];
