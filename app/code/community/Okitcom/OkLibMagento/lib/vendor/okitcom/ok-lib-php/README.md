[![Build Status](https://travis-ci.org/okitcom/ok-lib-php.svg?branch=master)](https://travis-ci.org/okitcom/ok-lib-php)
# OK LIB client
This is a PHP client implementation for the OK lib API. Compatible with PHP 5.5+, requires cURL php library.

## Features
The PHP library implements four OK services:
Open, Cash, Ticket and OKTicket.

## Usage
Instantiate a service from `OK\Service` with OKWORKS credentials

## Example

### Set up
First create the necessary credentials.

`$credentials = new OK\Credentials\CashCredentials(PUBLICKEY, PRIVATEKEY, ENVIRONMENT)`

Then, initiate the service.

`$cash = new OK\Service\Cash($credentials)`

Now, we can make requests to the OK API using the builder:

```
// Create a transaction with one line item
$request = (new TransactionBuilder())
            ->setAmount(Amount::fromCents(1000))
            ->setReference("internal reference")
            ->addLineItem(
                (new LineItemBuilder())
                    ->setAmount(Amount::fromEuro(10.00))
                    ->setVat(0)
                    ->setCurrency("EUR")
                    ->setQuantity(1)
                    ->setDescription("Awesome product")
                    ->build()
            )
            ->build();
            
$cash->request($request);
```

### OK Open
To make an authorisation request:
```
// Set up credentials
$credentials = new OK\Credentials\OpenCredentials(PUBLICKEY, PRIVATEKEY, ENVIRONMENT);
$open = new OK\Service\Open($credentials)
  
$request = (new AuthorisationRequestBuilder())
            ->setAction("Login")
            ->setReference("reference")
            ->setPermissions("NewPendingTrigger")
            ->setLocation(
                (new LocationBuilder())
                    ->setLat(1.2345)
                    ->setLon(5.1231)
                    ->build()
            )
            ->addAttribute(
                (new AttributeBuilder())
                ->setType(Attribute::TYPE_NAME)
                ->setKey("name")
                ->setLabel("Name")
                ->setRequired(true)
                ->build()
            )
            ->build();
              
// Submit the request
$response = $open->request($request);
```


## Testing
The package includes PHPUnit tests. In order to run the tests, include the credentials in the environment variables. The following credentials exist:

```
KEY_OPEN
KEY_CASH
KEY_TICKET
```