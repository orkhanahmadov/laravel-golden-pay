<?php

namespace Orkhanahmadov\LaravelGoldenPay\Console;

use App\Console\Kernel as ConsoleKernel;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Orkhanahmadov\LaravelGoldenPay\GoldenPay;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        parent::schedule($schedule);

        $schedule->call(function () {
            (new GoldenPay)->manualPaymentCheck();
        })->everyThirtyMinutes();


        if (config('goldenpay.delete_unused'))
            $schedule->call(function () {
                (new GoldenPay)->deleteUnusedPayments();
            })->daily();
    }
}