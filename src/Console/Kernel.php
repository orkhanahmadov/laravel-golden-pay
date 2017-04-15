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
            Log::info('CRON: manualPaymentCheck - ' . date('d.m.Y H:i'));

            (new GoldenPay)->manualPaymentCheck();
        })->everyMinute(); // TODO: change to everyThirtyMinutes


        if (config('goldenpay.delete_unused'))
            $schedule->call(function () {
                Log::info('CRON: deleteUnusedPayments - ' . date('d.m.Y H:i'));

                (new GoldenPay)->deleteUnusedPayments();
            })->everyMinute(); // TODO: change to daily
    }
}