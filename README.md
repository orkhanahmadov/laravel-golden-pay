# Laravel GoldenPay package

This package built for receiving payments with Azerbaijani payment processing service GoldenPay.

### Installation

Run Composer command:

```composer
composer require orkhanahmadov/laravel-golden-pay
```

Add this line to your provider list (`app/config.app`):

```php
Orkhanahmadov\LaravelGoldenPay\LaravelGoldenPayServiceProvider::class,
```

Add this line to your aliases list (`app/config.app`):

```php
'GoldenPay' => Orkhanahmadov\LaravelGoldenPay\Facade\GoldenPay::class,
```

Add following lines to your `.env` file and fill their values:
```
GOLDEN_PAY_MERCHANT_NAME=
GOLDEN_PAY_AUTH_KEY=
GOLDEN_PAY_CALLBACK_SUCCESS_URL=
GOLDEN_PAY_CALLBACK_FAIL_URL=
GOLDEN_PAY_REDIRECT_ROUTE=
GOLDEN_PAY_DELETE_UNUSED=
```
  - `GOLDEN_PAY_MERCHANT_NAME` - Merchant name given by GoldenPay
  - `GOLDEN_PAY_AUTH_KEY` - Auth key given by GoldenPay. You can get it from [GoldenPay merchant page](https://rest.goldenpay.az/merchant/).
  - `GOLDEN_PAY_CALLBACK_URL` - Relative Callback URL for successful payments. Example: `"/payment/goldenpay/success"`. Use full URL on [GoldenPay merchant page](https://rest.goldenpay.az/merchant/).
  - `GOLDEN_PAY_CALLBACK_FAIL_URL` - Relative Callback URL for failed payments. Example: `"/payment/goldenpay/fail"`. Use full URL on [GoldenPay merchant page](https://rest.goldenpay.az/merchant/).
  - `GOLDEN_PAY_REDIRECT_ROUTE` - Route name where redirect payment data when finishes
  - `GOLDEN_PAY_DELETE_UNUSED` - (optional, default - `true`) Delete unused payment initialisations from database every day (requires Laravel Cron setup to work)
  

Lastly you need to migrate required tables to database with artisan command:
```
php artisan migrate
```

### Initializing payment
To initialize payment you need to call `init` method. Method accepts array as its only parameter.
Array must have:
  - `amount` - payment amount
  - `cardType` - which payment card will be used, `v` for VISA, `m` - MasterCard
  - `description` - description about purchase or item name or item ID
  - `lang` - (optional) defines which locale will be used on payment page. `lv` for Azerbaijani, `ru` for Russian and `en` for English. This is optional and if not set package will use Laravel's built-in `App::getLocale()` to set locale.
```php
GoldenPay::init([
    'amount' => 15.5,
    'cardType' => 'v',
    'description' => 7
]);
```
Method will return unique payment URL. Use that url to redirect user to payment page.

All payment processing is done GoldenPay's side. Once user finishes payment GoldenPay's processing center will redirect user to one of the callback URLs depending on payment result.
Package will handle payment result, insert it into database table set session data will payment result, and redirect user to `GOLDEN_PAY_REDIRECT_ROUTE` route.
Session data will contain:
  - `goldenpay_status_code` - Payment result code. `1` on success.
  - `goldenpay_status_message` - Payment result message. `success` on success.
  - `goldenpay_amount` - Amount paid
  - `goldenpay_description` - Item description
  - `goldenpay_reference_number` - GoldenPay's unique reference number


### Handling unfinished payments
Usually when user is done on payment page result will be send to callback URL and result will be handled.
But there's a chance that for some reason callback functions won't get result about payment. 
Like when website is down, or in maintenance mode or user leaves payment page without finishing payment.
In cases like these those unfinished payments need to be checked manually.
If you have already enabled Cron for Laravel, package will check statuses for unfinished payments automatically.
Learn more about how to enable Cron for Laravel [here](https://laravel.com/docs/5.4/scheduling#introduction)


### Config
You can dump config files to your root `config` directory with artisan command:
```
php artisan vendor:publish
```
This command will create `goldenpay` folder inside your root `config` directory and dump all config files.

###License
MIT

### Todo

 - Send SMS on successful payment
