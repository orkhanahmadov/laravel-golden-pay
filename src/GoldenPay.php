<?php

namespace Orkhanahmadov\LaravelGoldenPay;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\App;
use Orkhanahmadov\LaravelGoldenPay\Model\GoldenPay as GoldenPayModel;
use Orkhanahmadov\LaravelGoldenPay\Model\Payment;

/**
 * Class GoldenPay
 * @package Orkhanahmadov\LaravelGoldenPay
 */
class GoldenPay extends Controller
{
    /**
     * @var string
     */
    private $merchantName;
    /**
     * @var string
     */
    private $authKey;

    /**
     * @var string
     */
    private $paymentKeyUrl;
    /**
     * @var string
     */
    private $paymentResultUrl;
    /**
     * @var string
     */
    private $paymentPageUrl;


    /**
     * GoldenPay constructor.
     */
    public function __construct()
    {
        $this->middleware('web');

        $this->merchantName = config('goldenpay.merchant_name');
        $this->authKey = config('goldenpay.auth_key');

        $this->paymentKeyUrl = config('goldenpay.payment_key_url');
        $this->paymentResultUrl = config('goldenpay.payment_result_url');
        $this->paymentPageUrl = config('goldenpay.payment_page_url');
    }


    /**
     * Initializer function.
     * $initData array requires values for:
     * 'amount'
     * 'cardType'
     * 'description'
     *
     * @param array $initData
     * @return string
     */
    public function init($initData) {
        $initData['merchantName'] = $this->merchantName;
        $initData['amount']       = $initData['amount'] * 100;
        $initData['hashCode']     = $this->getHashCode($initData);

        $initData['lang'] = $this->getLocale($initData['lang']);

        return $this->getPaymentKey($this->paymentKeyUrl, $initData);
    }


    /**
     * Successful payments handler
     *
     * @return array
     */
    public function paymentSuccess()
    {
        return $this->paymentResult(request()->query('payment_key'), true);
    }


    /**
     * Failed payments handler
     *
     * @return array
     */
    public function paymentFail()
    {
        return $this->paymentResult(request()->query('payment_key'));
    }


    /**
     * Every 30 minutes check payment statuses
     */
    public function manualPaymentCheck()
    {
        $uncheckedPayments = GoldenPayModel::whereNull('reference_number')->get();

        foreach ($uncheckedPayments as $payment) {
            $paymentResult = $this->getPaymentStatus($payment->payment_key);

            $collect = [
                'payment_status_code' => $paymentResult['status']['code'],
                'payment_status_message' => $paymentResult['status']['message'],
                'reference_number' => $paymentResult['rrn'],
                'payment_date' => $paymentResult['paymentDate'],
                'check_count' => $paymentResult['checkCount'] + 1
            ];

            if (!empty($paymentResult['cardNumber']))
                $collect['card_number'] = encrypt($paymentResult['cardNumber']);

            $payment->update($collect);

            if ($paymentResult['status']['code'] == 1)
                $payment->payment()->save(new Payment([
                    'amount' => $paymentResult['amount'] / 100,
                    'item' => $paymentResult['description']
                ]));
        }
    }


    /**
     * Delete unused GoldenPay initialisations
     */
    public function deleteUnusedPayments()
    {
        GoldenPayModel::wherePaymentStatusCode(819)->delete();
    }


    /**
     * Set locale for payment page
     *
     * @param bool|string $lang
     * @return string
     */
    private function getLocale($lang = false)
    {
        if ($lang)
            return $lang;

        $locale = App::getLocale();

        if ($locale == 'az')
            $locale = 'lv';

        return $locale;
    }


    /**
     * Get new paymentKey
     *
     * @param string $url
     * @param array $initData
     * @return string
     */
    private function getPaymentKey($url, $initData)
    {
        $request = new GuzzleRequest(
            'POST',
            $url,
            ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            json_encode($initData)
        );

        $response = (new Client())->send($request);

        $result = json_decode($response->getBody()->getContents(), true);

        GoldenPayModel::create([
            'card_type' => $initData['cardType'],
            'payment_key' => $result['paymentKey'],
            'language' => $initData['lang']
        ]);

        return $this->paymentPageUrl . $result['paymentKey'];
    }


    /**
     * Get payment result from payment
     *
     * @param string $paymentKey
     * @param bool $success
     * @return array
     */
    private function paymentResult($paymentKey, $success = false)
    {
        $paymentResult = $this->getPaymentStatus($paymentKey);

        $goldenPayPayment = GoldenPayModel::wherePaymentKey($paymentKey)->first();


        $collect = [
            'payment_status_code' => $paymentResult['status']['code'],
            'payment_status_message' => $paymentResult['status']['message'],
            'card_number' => encrypt($paymentResult['cardNumber']),
            'reference_number' => $paymentResult['rrn'],
            'payment_date' => $paymentResult['paymentDate'],
            'check_count' => $paymentResult['checkCount'] + 1
        ];

        if (!$goldenPayPayment->result_received)
            $goldenPayPayment->update($collect);

        if ($success && !$goldenPayPayment->result_received)
            $goldenPayPayment->payment()->save(new Payment([
                'amount' => $paymentResult['amount'] / 100,
                'item' => $paymentResult['description']
            ]));

        session([
            'goldenpay_status_code' => $paymentResult['status']['code'],
            'goldenpay_status_message' => $paymentResult['status']['message'],
            'goldenpay_amount' => $paymentResult['amount'] / 100,
            'goldenpay_description' => $paymentResult['description'],
            'goldenpay_reference_number' => $paymentResult['rrn']
        ]);

        return redirect()->route(config('goldenpay.redirect_route'));
    }


    /**
     * Get payment payment status with defined paymentKey
     *
     * @param string $paymentKey
     * @return mixed
     */
    private function getPaymentStatus($paymentKey)
    {
        $params['payment_key'] = $paymentKey;
        $params['hash_code'] = $this->getHashCode($params);

        $request = new GuzzleRequest(
            'GET',
            $this->paymentResultUrl,
            ['Content-Type' => 'application/json', 'Accept' => 'application/json']
        );

        $response = (new Client())->send($request, ['query' => $params]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * @param array $params
     * @return string
     */
    private function getHashCode($params)
    {
        if (isset($params['payment_key']))
            $hash = md5(
                $this->authKey .
                $params['payment_key']
            );

        else
            // this is used to init payment - get payment key
            $hash = md5(
                $this->authKey .
                $this->merchantName .
                $params['cardType'] .
                $params['amount'] .
                $params['description']
            );

        return $hash;
    }
}