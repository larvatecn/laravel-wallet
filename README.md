# laravel-wallet

Wallet Extension for Laravel. Laravel 余额钱包

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
