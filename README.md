# laravel-wallet

Laravel 余额

## 环境需求

- PHP >= 7.1.3

## Installation

```bash
composer require larva/laravel-wallet -vv
```

## for Laravel

This service provider must be registered.

```php
// config/app.php

'providers' => [
    '...',
    Larva\Wallet\WalletServiceProvider::class,
];
```

## Config
```php
add services.php
'wallet'=>[
    'withdrawals_mix' => 100,//最小提现数
],
```





