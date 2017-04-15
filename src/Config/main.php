<?php

return [
    'merchant_name'         => env('GOLDEN_PAY_MERCHANT_NAME'),
    'auth_key'              => env('GOLDEN_PAY_AUTH_KEY'),
    'callback_success_url'  => env('GOLDEN_PAY_CALLBACK_SUCCESS_URL'),
    'callback_fail_url'     => env('GOLDEN_PAY_CALLBACK_FAIL_URL'),
    'redirect_route'        => env('GOLDEN_PAY_REDIRECT_ROUTE'),
    'delete_unused'         => env('GOLDEN_PAY_DELETE_UNUSED', true),

    "payment_key_url"       => "https://rest.goldenpay.az/web/service/merchant/getPaymentKey",
    "payment_result_url"    => "https://rest.goldenpay.az/web/service/merchant/getPaymentResult",
    "payment_page_url"      => "https://rest.goldenpay.az/web/paypage?payment_key="
];