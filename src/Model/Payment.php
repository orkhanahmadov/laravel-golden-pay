<?php

namespace Orkhanahmadov\LaravelGoldenPay\Model;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['amount', 'item'];


    public function payable () {
        return $this->morphTo();
    }
}
