<?php

class BackoffTest extends PHPUnit_Framework_TestCase
{
    protected $backoff;
    protected $backoffDefaults = [
        'min' => 5,
        'max' => 100,
        'factor' => 3,
        'jitter' => true,
        'jitterMax' => 10,
    ];

    public function setUp()
    {
        $this->backoff = new nevsnode\Backoff($this->backoffDefaults);
    }

    public function testSetGetMin()
    {
        $this->backoff->setMin(1);
        $this->assertEquals(1, $this->backoff->getMin());

        $this->backoff->setMin(9);
        $this->assertEquals(9, $this->backoff->getMin());

        $this->backoff->setMin(-2);
        $this->assertEquals(1, $this->backoff->getMin());
    }

    public function testSetGetMax()
    {
        $this->backoff->setMax(1);
        $this->assertEquals(1, $this->backoff->getMax());

        $this->backoff->setMax(9);
        $this->assertEquals(9, $this->backoff->getMax());

        $this->backoff->setMax(-9);
        $this->assertEquals($this->backoffDefaults['min'], $this->backoff->getMax());
    }

    public function testSetGetFactor()
    {
        $this->backoff->setFactor(1);
        $this->assertEquals(1, $this->backoff->getFactor());

        $this->backoff->setFactor(9);
        $this->assertEquals(9, $this->backoff->getFactor());

        $this->backoff->setFactor(-9);
        $this->assertEquals(1, $this->backoff->getFactor());
    }

    public function testSetGetJitter()
    {
        $this->backoff->setJitter(true);
        $this->assertEquals(true, $this->backoff->getJitter());

        $this->backoff->setJitter(false);
        $this->assertEquals(false, $this->backoff->getJitter());
    }

    public function testSetGetJitterMax()
    {
        $this->backoff->setJitterMax(1);
        $this->assertEquals(1, $this->backoff->getJitterMax());

        $this->backoff->setJitterMax(9);
        $this->assertEquals(9, $this->backoff->getJitterMax());

        $this->backoff->setJitterMax(-3);
        $this->assertEquals(0, $this->getJitterMax());
    }

    public function testDefaults()
    {
        $this->assertEquals($this->backoffDefaults['min'], $this->backoff->getMin());
        $this->assertEquals($this->backoffDefaults['max'], $this->backoff->getMax());
        $this->assertEquals($this->backoffDefaults['factor'], $this->backoff->getFactor());
        $this->assertEquals($this->backoffDefaults['jitter'], $this->backoff->getJitter());
        $this->assertEquals($this->backoffDefaults['jitterMax'], $this->backoff->getJitterMax());
    }

    public function testAddDelayWithoutJitter()
    {
        $this->backoff->setJitter(false);

        $this->assertEquals(0, $this->backoff->getDelay());

        // first delay should be the minimum value
        $this->backoff->addDelay();
        $this->assertEquals($this->backoffDefaults['min'], $this->backoff->getDelay());

        for ($i = 1; $i <= 3; $i++) {
            // following delays should be the number of delays * minimum value
            $delay = $this->backoffDefaults['min'] * $i;
            // (and applying the defined factor)
            $delay *= $this->backoffDefaults['factor'];

            $this->backoff->addDelay();
            $this->assertEquals($delay, $this->backoff->getDelay());
        }

        for ($i = 0; $i < 50; $i++) {
            $this->backoff->addDelay();
        }
        $this->assertEquals($this->backoffDefaults['max'], $this->backoff->getDelay());

        $this->backoff->resetDelay();
        $this->assertEquals(0, $this->backoff->getDelay());

        $this->backoff->addDelay();
        $this->assertEquals($this->backoffDefaults['min'], $this->backoff->getDelay());
    }

    protected function isBetween($i, $a, $b)
    {
        return ($i >= $a && $i <= $b);
    }

    public function testAddDelayWithJitter()
    {
        // TODO
    }

    public function testDelayFactor()
    {
        // TODO
    }
}
