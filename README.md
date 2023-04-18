# Sendy API PHP Client

PHP Client library to connect with the Sendy API. This client lets you connect with the Sendy API to create shipments
and labels.

[![Code quality](https://github.com/keendelivery/php-sdk/actions/workflows/code_quality.yml/badge.svg)](https://github.com/keendelivery/php-sdk/actions/workflows/code_quality.yml) [![Tests](https://github.com/keendelivery/php-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/keendelivery/php-sdk/actions/workflows/tests.yml)

## Installation
Installing the client can be done through Composer:
```
composer require sendynl/php-sdk
```

## Usage

### Authentication

Authentication can be done in two different ways: with personal access tokens and the OAuth flow. 

#### Personal Access Tokens

You can manage your personal access tokens in [the portal](https://app.sendy.nl/settings/personal-access-tokens). When
you have a personal access token, you can use this library like this:

```php
<?php

$connection = new \Sendy\Api\Connection();

$connection->setAccessToken('your-personal-access-token');

$connection->me->get();
```

#### OAuth

##### Authorize the connection from your app

```php
$connection = new \Sendy\Api\Connection();

$connection->setOauthClient(true);

$connection->setClientId('your-client-id')
    ->setClientSecret('your-client-secret')
    ->setRedirectUrl('your-callback-url');

$connection->redirectForAuthorization();
```

This will redirect the user to the portal and authorize your integration with their account.

##### Connect with the API

```php
$connection = new \Sendy\Api\Connection();

$connection->setClientId('your-client-id')
    ->setClientSecret('your-client-secret')
    ->setRedirectUrl('your-callback-url');

// Either an authorization code or a refresh token is required to fetch an access token
$connection->setAuthorizationCode('authorization-code');
$connection->setRefreshToken('refresh-token');

// Optional if you have alreay stored the access token in your application
$connection->setAccessToken('access-token');
$connection->setTokenExpires(1123581321);

$connection->me->get();

// Store the access token, refresh token en the expiration timestamp in your application to prevent unnecessary
// requesting new access tokens
$connection->getAccessToken();
$connection->getRefreshToken();
$connection->getTokenExpires();
```

##### Token refresh callback
It is possible to provide the connection a `callable` to execute when the tokens are refresh. This can be useful when 
you want to store the new tokens in your application to reuse them when necessary.

```php
$connection = new \Sendy\Api\Connection();

$connection->setOauthClient(true);

$connection->setClientId('your-client-id')
    ->setClientSecret('your-client-secret')
    ->setRedirectUrl('your-callback-url');
    
$tokenUpdateCallback = function (\Sendy\Api\Connection $connection) {
    $data = [
        'access_token' => $connection->getAccessToken(),
        'refresh_token' => $connection->getRefreshToken(),
        'token_expires' => $connection->getTokenExpires(),
    ];
    
    // Use this data to store the tokens in your application
};
```

### Endpoints

The endpoints in the API documentation are mapped to the resource as defined in the Resources directory. Please consult
the [API documentation](https://app.sendy.nl/api/docs) for detailed documentation. 

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you've found a bug regarding security please mail security@sendy.nl instead of using the issue tracker.

## License

The MIT License (MIT). Please see the [License file](LICENSE) for more information
