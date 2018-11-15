<?php

namespace DeskPRO\Component\Util;

class Retry
{
    /**
     * @var int
     */
    private $maxTries = 3;

    /**
     * @var bool
     */
    private $throwLast = true;

    /**
     * @var bool
     */
    private $returnResult = false;

    /**
     * @return Retry
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param int $maxTries
     *
     * @return Retry
     */
    public function maxTries($maxTries)
    {
        $this->maxTries = $maxTries;

        return $this;
    }

    /**
     * @return Retry
     */
    public function throwLast()
    {
        $this->throwLast = true;

        return $this;
    }

    /**
     * @return Retry
     */
    public function dontThrowLast()
    {
        $this->throwLast = false;

        return $this;
    }

    /**
     * Return a RetryResult (e.g. so you can inspect the errors).
     *
     * @return Retry
     */
    public function returnRetryResult()
    {
        $this->returnResult = true;

        return $this;
    }

    /**
     * Return the actual value.
     * If you dont have throwLast then this will be null.
     *
     * @return $this
     */
    public function returnValue()
    {
        $this->returnResult = false;

        return $this;
    }

    /**
     * @param callable $fn
     *
     * @return RetryResult
     */
    public function run($fn)
    {
        $ex     = [];
        $lastEx = null;
        $value  = null;

        for ($i = 0; $i < $this->maxTries; ++$i) {
            try {
                $value = call_user_func($fn, [
                    'attempt'          => $i,
                    'isFirst'          => $i === 0,
                    'isLast'           => $i === ($this->maxTries - 1),
                    'lastException'    => $lastEx,
                    'lastErrorMessage' => $lastEx ? $lastEx->getMessage() : '',
                ]);
                break;
            } catch (\Exception $e) {
                $lastEx = $e;
                $ex[]   = $e;
            }
        }

        if (count($ex) === $this->maxTries) {
            if ($this->throwLast) {
                throw $lastEx;
            }

            $r = RetryResult::newNoValue($ex);
        } else {
            $r = RetryResult::newWithValue($value, $ex);
        }

        if ($this->returnResult) {
            return $r;
        }

        // otherwise user wants the actual
        // value, which only makes sense if it exists
        if ($r->isError()) {
            return null;
        }

        return $r->getValue();
    }
}

class RetryResult
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var \Exception[]
     */
    private $exceptions = [];

    /**
     * @param \Exception[] $exceptions
     *
     * @return RetryResult
     */
    public static function newNoValue(array $exceptions)
    {
        if (count($exceptions) < 1) {
            throw new \InvalidArgumentException('You must specify at least one exception to explain lack of value');
        }

        return new self(NullValue::get(), $exceptions);
    }

    /**
     * @param mixed        $value
     * @param \Exception[] $exceptions
     */
    public static function newWithValue($value = null, array $exceptions = [])
    {
        return new self($value, $exceptions);
    }

    /**
     * @param mixed        $value
     * @param \Exception[] $exceptions
     */
    private function __construct($value, array $exceptions)
    {
        $this->value      = $value;
        $this->exceptions = $exceptions;
    }

    /**
     * Was there an error that prevented the value?
     *
     * This is simply the inverse of hasValue.
     *
     * @return bool
     */
    public function isError()
    {
        return NullValue::is($this->value);
    }

    /**
     * @return bool
     */
    public function hasException()
    {
        return count($this->exceptions) > 0;
    }

    /**
     * Gets the last exception that was thrown.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->exceptions ? ListUtils::last($this->exceptions) : null;
    }

    /**
     * @return \Exception[]
     */
    public function getAllExceptions()
    {
        return $this->exceptions;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (NullValue::is($this->value)) {
            throw new \RuntimeException('Call failed, you cannot get the value');
        }

        return $this->value;
    }

    /**
     * @return bool
     */
    public function hasValue()
    {
        return !NullValue::is($this->value);
    }
}
