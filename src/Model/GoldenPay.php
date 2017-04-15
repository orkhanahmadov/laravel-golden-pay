<?php

namespace Orkhanahmadov\LaravelGoldenPay\Model;

use Illuminate\Database\Eloquent\Model;

class GoldenPay extends Model
{
    protected $fillable = [
        'card_type',
        'payment_key',
        'language',

        'payment_status_code',
        'payment_status_message',
        'card_number',
        'reference_number',

        'payment_date',
        'check_count',
    ];

    protected $hidden = ['card_number'];

    public function payment () {
        return $this->morphOne('Orkhanahmadov\LaravelGoldenPay\Model\Payment', "payable");
    }
}
