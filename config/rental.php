<?php

return [
    'max_active_contracts' => env('RENTAL_MAX_ACTIVE_CONTRACTS', 3),
    'max_outstanding_balance' => env('RENTAL_MAX_OUTSTANDING_BALANCE', 10000),
    'min_credit_score' => env('RENTAL_MIN_CREDIT_SCORE', 50),
    'late_payment_grace_period_days' => env('RENTAL_LATE_PAYMENT_GRACE_PERIOD', 5),
    'overdue_penalty_rate' => env('RENTAL_OVERDUE_PENALTY_RATE', 0.1), // 10% per day
];