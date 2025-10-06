<?php
// Payment Configuration
return [
    'currency' => 'UGX', // Ugandan Shillings
    'default_providers' => [
        'mobile_money' => 1, // MTN Mobile Money
        'bank_transfer' => 3, // Centenary Bank
    ],
    
    // MTN Mobile Money API Configuration
    'mtn_momo' => [
        'base_url' => 'https://sandbox.momodeveloper.mtn.com', // Use sandbox for testing
        'subscription_key' => 'YOUR_MTN_SUBSCRIPTION_KEY',
        'api_key' => 'YOUR_MTN_API_KEY',
        'target_environment' => 'sandbox', // or 'live'
        'callback_url' => 'http://yourdomain.com/payment_callback.php',
    ],
    
    // Airtel Money API Configuration
    'airtel_money' => [
        'base_url' => 'https://openapi.airtel.africa',
        'client_id' => 'YOUR_AIRTEL_CLIENT_ID',
        'client_secret' => 'YOUR_AIRTEL_CLIENT_SECRET',
        'country' => 'UG',
        'currency' => 'UGX',
    ],
    
    // Bank Transfer Configuration
    'bank_transfer' => [
        'account_name' => 'KBMO Center for Translational Research',
        'account_number' => '31003456789',
        'bank_name' => 'Centenary Bank',
        'branch' => 'Gulu Main Branch',
        'swift_code' => 'CBEUUGKA',
    ]
];
?>