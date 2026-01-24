<?php

return [
    'url' => env('ML_API_BASE_URL'),
    'key' => env('ML_API_KEY'),
    'timeout' => env('ML_API_TIMEOUT', 60),
    'ca_bundle' => env('CURL_CA_BUNDLE'),
];
