<?php

namespace nevsnode;

class Backoff
{
    public static $defaults = [];

    protected static $_defaults = [
        'min' => 1000,
        'max' => 30000,
        'factor' => 2,
        'jitter' => true,
        'jitterMax' => 2000,
    ];

    protected $min;
    protected $max;
    protected $factor;
    protected $jitter;
    protected $jitterMax;
    protected $exceptions = [];

    private $attempt = 0;
    private $delay = 0;

    public function __construct(array $params = [])
    {
        foreach (self::$_defaults as $key => $val) {
            $method = 'set' . ucfirst($key);

            if (isset(self::$defaults[$key])) {
                $val = self::$defaults[$key];
            }
            if (isset($params[$key])) {
                $val = $params[$key];
            }

            call_user_func([$this, $method], $val);
        }
    }

    public function __call($name, $args)
    {
        if (0 !== strpos($name, 'get')) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
            return;
        }

        $val = lcfirst(substr($name, 3));
        if (!isset(self::$_defaults[$val])) {
            return false;
        }

        return $this->$val;
    }

    public function setMin($min)
    {
        $this->min = (int)$min;

        if ($this->min < 1) {
            $this->min = 1;
        }

        return $this;
    }

    public function setMax($max)
    {
        $this->max = (int)$max;

        if ($this->max < $this->min) {
            $this->max = $this->min;
        }

        return $this;
    }

    public function setFactor($factor)
    {
        $this->factor = (float)$factor;

        if ($this->factor < 1) {
            $this->factor = 1;
        }

        return $this;
    }

    public function setJitter($jitter)
    {
        $this->jitter = (bool)$jitter;

        return $this;
    }

    public function setJitterMax($jitterMax)
    {
        $this->jitterMax = (int)$jitterMax;

        if ($this->jitterMax < 0) {
            $this->jitterMax = 0;
        }

        return $this;
    }

    public function setExceptions(array $exceptions)
    {
        $this->exceptions = $exceptions;
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

    public function retryOnException($retries, callable $func, $final = null)
    {
        $catch = function($i, $e) use ($retries, $func, $final) {
            if (!empty($this->exceptions)) {
                if (!in_array(get_class($e), $this->exceptions)) {
                    throw $e;
                }
            }

            if ($i + 1 === $retries) {
                $this->resetDelay();

                if (isset($final)) {
                    if (is_callable($final)) {
                        return call_user_func($final, $e);
                    }
                    return $final;
                }

                throw $e;
            }

            $this->addDelay();

            return null;
        };

        for ($i = 0; $i < $retries; $i++) {
            usleep($this->getDelay() * 1000);

            try {
                $return = call_user_func($func);

                $this->resetDelay();
                return $return;
            } catch (\Throwable $t) {
                $return = $catch($i, $t);
            } catch (\Exception $e) {
                $return = $catch($i, $e);
            }

            if (isset($return)) {
                return $return;
            }
        }
    }
}
