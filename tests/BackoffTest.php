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
        $this->assertEquals(0, $this->backoff->getJitterMax());
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
        $this->runTestDelay(false);
    }

    public function testAddDelayWithJitter()
    {
        $this->runTestDelay(true);
    }

    public function testDelayFactor()
    {
        $cases = [
            [false, 1],
            [true,  1],
            [false, 2],
            [true,  2],
            [false, 3],
            [true,  3],
            [false, M_E],
            [true,  M_E],
        ];

        foreach ($cases as $case) {
            $this->runTestDelay($case[0], $case[1]);
        }
    }

    protected function runTestDelay($withJitter, $factor = null)
    {
        $this->backoff->setJitter($withJitter);

        if (!isset($factor)) {
            $factor = $this->backoff->getFactor();
        }

        // first delay should be the between minimum value and minimum value + jitter-max
        $this->backoff->addDelay();
        $max = $this->backoffDefaults['min'];
        if ($withJitter) {
            $max += $this->backoffDefaults['jitterMax'];
        }
        $this->isBetween($this->backoff->getDelay(), $this->backoffDefaults['min'], $max);

        for ($i = 1; $i <= 3; $i++) {
            // following delays should be the number of delays * minimum value
            $delay = $this->backoffDefaults['min'] * $i;
            // (and applying the defined factor)
            $delay *= $factor;

            // the returned delay should be at most the 'regular' delay + jitter-max
            $max = $delay;
            if ($withJitter) {
                $max += $this->backoffDefaults['jitterMax'];
            }

            $this->backoff->addDelay();
            $this->isBetween($this->backoff->getDelay(), $delay, $max);
        }

        for ($i = 0; $i < 50; $i++) {
            $this->backoff->addDelay();
        }
        $this->assertEquals($this->backoffDefaults['max'], $this->backoff->getDelay());

        $this->backoff->resetDelay();
        $this->assertEquals(0, $this->backoff->getDelay());

        $this->backoff->addDelay();
        $max = $this->backoffDefaults['min'];
        if ($withJitter) {
            $max += $this->backoffDefaults['jitterMax'];
        }
        $this->isBetween($this->backoff->getDelay(), $this->backoffDefaults['min'], $max);
    }

    protected function isBetween($i, $a, $b)
    {
        $this->assertGreaterThanOrEqual($a, $i);
        $this->assertLessThanOrEqual($b, $i);
    }
}
