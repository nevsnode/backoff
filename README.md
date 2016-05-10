nevsnode/Backoff
===

Very simple Backoff PHP library. It provides an easy solution to wait after failures with an increasing delay.

Install
---

```sh
composer require nevsnode/backoff
```

Usage
---

```php
<?php
use nevsnode\Backoff;

$backoff = new Backoff();

while (true) {
    $delay = $backoff->getDelay()

    // ...

    if ($success) {
        $backoff->resetDelay();
    } else {
        $backoff->addDelay();
    }
}
```

#### Example

*Note:* This example just repeats failing items which may lead to an infinite loop.
Depending on your use-case I recommend to add an additional check that breaks the for-loop after a certain maximum number of iterations.

```php
<?php
use nevsnode\Backoff;

$backoff = new Backoff();

$items = [];
for ($i = 0, $c = count($items); $i < $c; ) {
    if ($delay = $backoff->getDelay()) {
        sleep($delay);
    }

    $item = $items[$i];
    $success = handleItem($item);

    if ($success) {
        $backoff->resetDelay();
        $i++;
    } else {
        $backoff->addDelay();
    }
}
```

Settings
---

Setting|Type|Description
------|----|-----------
min|Integer|Minimum delay
max|Integer|Maximum delay
factor|Float|Multiplicator of delay on additional delays
jitter|Boolean|Allow jitter
jitterMax|Integer|Maximum jitter


The settings can be passed as an associative array to the constructor and returned or adjusted after instantiation:

```php
<?php

// pass settings to constructor
$backoff = new Backoff([
    'min' => 2,
    'max' => 10,
    'factor' => M_E,
]);

// define setting through setter
$backoff->setJitter(true);
$backoff->setJitterMax(6);
$backoff->setMin(3);

// return setting through getter
$max = $backoff->getMax();
```
