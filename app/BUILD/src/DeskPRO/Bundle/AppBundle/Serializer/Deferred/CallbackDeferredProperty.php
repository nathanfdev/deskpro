<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Deferred;

/**
 * If a property is transformed to this class, we will run the callback and use its returned data as the
 * property value. We execute the callback after transformation in a later event.
 */
class CallbackDeferredProperty
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $arguments;

    /**
     * Constructor.
     *
     * @param callable $callable
     * @param array    $arguments
     */
    public function __construct(callable $callable, array $arguments = [])
    {
        $this->callable  = $callable;
        $this->arguments = $arguments;
    }

    /**
     * @return mixed
     */
    public function call()
    {
        return call_user_func_array($this->callable, $this->arguments);
    }
}
