<?php

/**
 * Hospital branding and labels used in layouts, login screen, and reports.
 *
 * Centralised here so we can change the display name in one place.
 */
return [

    'name' => 'Ronald Ross General Hospital',

    'section' => 'High Cost Section',

    'system_name' => 'High Cost Billing System',

    /*
    |--------------------------------------------------------------------------
    | Session timeout (minutes)
    |--------------------------------------------------------------------------
    |
    | Matches SESSION_LIFETIME in .env. Shown on the login page so staff know
    | how long an idle session lasts before they must sign in again.
    |
    */
    'session_lifetime_minutes' => (int) env('SESSION_LIFETIME', 120),

    /*
    |--------------------------------------------------------------------------
    | Large deposit confirmation threshold (Kwacha)
    |--------------------------------------------------------------------------
    |
    | Deposits at or above this amount require staff to tick a confirmation
    | checkbox before the deposit can be saved.
    |
    */
    'large_deposit_threshold' => (float) env('LARGE_DEPOSIT_THRESHOLD', 10000),

    /*
    |--------------------------------------------------------------------------
    | Low balance alert threshold (Kwacha)
    |--------------------------------------------------------------------------
    |
    | Nursing staff see a warning when the payer account balance falls below
    | this amount before or after posting a bill.
    |
    */
    'low_balance_threshold' => (float) env('LOW_BALANCE_THRESHOLD', 1000),

];
