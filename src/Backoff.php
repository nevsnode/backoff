<?php

namespace nevsnode;

class Backoff
{
    protected $min          = 1;
    protected $max          = 30;
    protected $factor       = 2;
    protected $jitter       = true;
    protected $jitterMax    = 2;

    private $attempt        = 0;
    private $delay          = 0;

    public function __construct(array $params = [])
    {
        $params = array_merge([
            'min'       => 1,
            'max'       => 30,
            'factor'    => 2,
            'jitter'    => true,
            'jitterMax' => 2,
        ], $params);

        $this->min          = (int)$params['min'];
        $this->max          = (int)$params['max'];
        $this->factor       = (int)$params['factor'];
        $this->jitter       = (bool)$params['jitter'];
        $this->jitterMax    = (int)$params['jitterMax'];

        if ($this->min < 1) {
            $this->min = 1;
        }
    }

    public function setMin($min)
    {
        $this->min = (int)$min;
    }

    public function setMax($max)
    {
        $this->max = (int)$max;
    }

    public function setFactor($factor)
    {
        $this->factor = (int)$factor;
    }

    public function setJitter($jitter)
    {
        $this->jitter = (bool)$jitter;
    }

    public function setJitterMax($jitterMax)
    {
        $this->jitterMax = (int)$jitterMax;
    }

    public function getMin()
    {
        return $this->min;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function getFactor()
    {
        return $this->factor;
    }

    public function getJitter()
    {
        return $this->jitter;
    }

    public function getJitterMax()
    {
        return $this->jitterMax;
    }

    public function addDelay()
    {
        $delay = ($this->factor * $this->attempt * $this->min);
        $this->attempt++;

        if ($delay < $this->min) {
            $delay = $this->min;
        }

        if ($this->jitter) {
            $delay += mt_rand(0, $this->jitterMax);
        }

        if ($delay > $this->max) {
            $delay = $this->max;
        }

        $this->delay = $delay;

        return $this;
    }

    public function resetDelay()
    {
        $this->attempt = 0;
        $this->delay = 0;

        return $this;
    }

    public function getDelay()
    {
        return $this->delay;
    }
}
