<?php

namespace Orkhanahmadov\LaravelGoldenPay\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class LaravelAzSmsSender
 * @package Orkhanahmadov\LaravelAzSmsSender\Facade
 */
class GoldenPay extends Facade {
    /**
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'laravelgoldenpay';
    }
}