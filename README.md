# WordPress User Agent Utilities

Work out what is making the request — including which AI crawler.

## What it does

The user agent string is the only thing a request tells you about the client,
and parsing it is tedious and easy to get wrong. Most libraries that do it are
enormous, because they try to identify every browser version ever shipped.

This answers the questions a plugin actually asks: is this a phone, is this a
bot, and — increasingly — is this an AI crawler you might want to treat
differently from Googlebot.

## Features

- Tell mobile, tablet and desktop apart
- Identify bots, and AI crawlers specifically
- Get a readable description of the client for a log or an order record
- Read the raw agent safely, unslashed and sanitised

## Installation

```bash
composer require arraypress/wp-user-agent-utils
```

## Quick start

```php
use ArrayPress\UserAgentUtils\Request;

// Record what the order was placed from.
update_post_meta( $order_id, '_device', Request::device_type() );

// Do not count AI crawlers as visitors.
if ( Request::is_ai_bot() ) {
    return;
}

// A readable line for the log.
$log->info( 'Checkout started', [ 'client' => Request::describe() ] );
```

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later

## License

GPL-2.0-or-later
