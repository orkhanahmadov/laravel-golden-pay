<?php

Route::get(config("goldenpay.callback_success_url"), 'Orkhanahmadov\LaravelGoldenPay\GoldenPay@paymentSuccess');
Route::get(config("goldenpay.callback_fail_url"), 'Orkhanahmadov\LaravelGoldenPay\GoldenPay@paymentFail');