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
        $defaults = [
            'min'       => 1,
            'max'       => 30,
            'factor'    => 2,
            'jitter'    => true,
            'jitterMax' => 2,
        ];

        foreach ($defaults as $key => $val) {
            $method = 'set' . ucfirst($key);
            if (isset($params[$key])) {
                $val = $params[$key];
            }
            call_user_func([$this, $method], $val);
        }
    }

    public function setMin($min)
    {
        $this->min = (int)$min;

        if ($this->min < 1) {
            $this->min = 1;
        }
    }

    public function setMax($max)
    {
        $this->max = (int)$max;

        if ($this->max < $this->min) {
            $this->max = $this->min;
        }
    }

    public function setFactor($factor)
    {
        $this->factor = (float)$factor;

        if ($this->factor < 1) {
            $this->factor = 1;
        }
    }

    public function setJitter($jitter)
    {
        $this->jitter = (bool)$jitter;
    }

    public function setJitterMax($jitterMax)
    {
        $this->jitterMax = (int)$jitterMax;

        if ($this->jitterMax < 0) {
            $this->jitterMax = 0;
        }
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
