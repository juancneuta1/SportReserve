<?php

return [
    'availability_opening_hour' => env('AVAILABILITY_OPENING_HOUR', '06:00'),
    'availability_closing_hour' => env('AVAILABILITY_CLOSING_HOUR', '22:00'),
    'availability_step_minutes' => (int) env('AVAILABILITY_STEP_MINUTES', 30),
];

