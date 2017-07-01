<?php

use PHPUnit\Framework\TestCase;

class BackoffRetryTest extends TestCase
{
    protected $backoff;

    public function setUp()
    {
        $this->backoff = new nevsnode\Backoff([
            'min' => 1000,
            'max' => 1000,
            'factor' => 1,
            'jitter' => false,
        ]);
    }

    public function testRetrySuccess()
    {
        $a = 0;

        $start = microtime(true);
        $b = $this->backoff->retryOnException(5, function () use (&$a) {
            $a++;
            return $a;
        });
        $duration = (int)floor(microtime(true) - $start);

        $this->assertEquals(0, $duration);
        $this->assertEquals(1, $a);
        $this->assertEquals(1, $b);
    }

    public function testRetrySuccessOnRetry()
    {
        $a = 0;

        $start = microtime(true);
        $b = $this->backoff->retryOnException(5, function () use (&$a) {
            $a++;

            if ($a <= 2) {
                throw new \Exception('Fake failure');
            }

            return $a;
        });
        $duration = (int)floor(microtime(true) - $start);

        $this->assertEquals(2, $duration);
        $this->assertEquals(3, $a);
        $this->assertEquals(3, $b);
    }

    public function testRetryFailure()
    {
        $a = 0;

        $e = new \Exception('Fake failure');
        $this->expectException(Exception::class);

        $start = microtime(true);
        try {
            $this->backoff->retryOnException(5, function () use (&$a, $e) {
                $a++;
                throw $e;
            });
        } catch (\Exception $ex) {
            $duration = (int)floor(microtime(true) - $start);
            $this->assertEquals(4, $duration);
            $this->assertEquals(5, $a);

            throw $ex;
        }
    }
}
