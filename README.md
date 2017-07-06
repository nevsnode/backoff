nevsnode/Backoff
===

Very simple Backoff PHP library. It provides an easy solution to wait after failures with an increasing delay.

Install
---

```sh
composer require nevsnode/backoff
```

Example Usage
---

```php
<?php
use nevsnode\Backoff;

$backoff = new Backoff();

$resource = new ExampleResource();
$result = $backoff->retryOnException(5, function () use ($resource) {
    $return = $resource->fetchSomething();
    if (!$return) {
        throw new Exception('Failed to fetch something');
    }
    return $return;
});
```

By default, when exceeding the number of retries, the last exception will just be thrown again.
Optionally a final closure/value can be defined which is executed or returned instead:

```php
<?php
$backoff = new nevsnode\Backoff();

// $result will now become the string "Some error"
$result = $backoff->retryOnException(3, function () {
    throw new \Exception('Some error');
}, function ($e) {
    return $e->getMessage();
});

// $result will now become FALSE
$result = $backoff->retryOnException(2, function () {
    throw new \Exception('Some error');
}, false);
```

Settings
---

Setting|Type|Default|Description
-------|----|-------|-----------
min|Integer|1000|Minimum delay (in ms)
max|Integer|30000|Maximum delay (in ms)
factor|Float|2.0|Multiplicator of delay on additional delays
jitter|Boolean|true|Allow jitter
jitterMax|Integer|2000|Maximum jitter (in ms)


The settings can be passed as an associative array to the constructor and returned or adjusted after instantiation:

```php
<?php

// pass settings to constructor
$backoff = new Backoff([
    'min' => 2000,
    'max' => 10000,
    'factor' => M_E,
]);

// define setting through setter
$backoff->setJitter(true);
$backoff->setJitterMax(6000);
$backoff->setMin(3000);

// return setting through getter
$max = $backoff->getMax();
```
