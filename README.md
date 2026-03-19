# MyAdmin Amazon Payments Plugin

[![Tests](https://github.com/detain/myadmin-amazon-payments/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-amazon-payments/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-amazon-payments/version)](https://packagist.org/packages/detain/myadmin-amazon-payments)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-amazon-payments/downloads)](https://packagist.org/packages/detain/myadmin-amazon-payments)
[![License](https://poser.pugx.org/detain/myadmin-amazon-payments/license)](https://packagist.org/packages/detain/myadmin-amazon-payments)

An Amazon Payments integration plugin for the [MyAdmin](https://github.com/detain/myadmin) control panel. This package provides support for processing payments through Amazon Pay, including wallet and address book widgets, OAuth-based profile retrieval, and configurable sandbox/live environment switching.

## Features

- Amazon Pay wallet and address book widget rendering
- OAuth 2.0 access token verification and user profile retrieval
- Configurable sandbox and live environment support
- Event-driven architecture via Symfony EventDispatcher hooks
- Admin settings panel for Client ID, Seller ID, and environment toggling

## Requirements

- PHP >= 5.0
- ext-soap
- ext-curl
- symfony/event-dispatcher ^5.0

## Installation

Install via Composer:

```sh
composer require detain/myadmin-amazon-payments
```

## Configuration

The plugin registers the following settings through the MyAdmin settings system:

| Setting                    | Description                          |
|----------------------------|--------------------------------------|
| `amazon_checkout_enabled`  | Enable or disable Amazon Checkout    |
| `amazon_sandbox`           | Toggle sandbox/test environment      |
| `amazon_client_id`         | Your Amazon Pay Client ID            |
| `amazon_seller_id`         | Your Amazon Pay Seller ID            |

## Usage

The plugin hooks into MyAdmin's event system automatically. Once installed, it registers:

- **system.settings** -- Adds Amazon payment configuration fields to the admin panel.
- **function.requirements** -- Registers the `amazon_obtain_profile`, `amazon_wallet_widget`, and `amazon_addressbook_widget` helper functions.

### Widget Functions

```php
// Render the Amazon address book widget
$html = amazon_addressbook_widget();

// Render the Amazon wallet/payment widget
$html = amazon_wallet_widget();
```

### Profile Retrieval

```php
// Validate an access token and retrieve the Amazon user profile
amazon_obtain_profile();
```

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

This package is licensed under the [LGPL-2.1](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.en.html) license.
