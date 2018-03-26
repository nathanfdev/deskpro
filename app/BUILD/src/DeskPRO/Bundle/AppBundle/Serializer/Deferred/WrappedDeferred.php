<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Deferred;

/**
 * Class WrappedDeferred.
 */
class WrappedDeferred
{
    /**
     * @var CallbackDeferredProperty
     */
    protected $deferred;

    /**
     * @var
     */
    protected $type;

    /**
     * WrappedDeferred constructor.
     *
     * @param CallbackDeferredProperty $deferred
     * @param array                    $type
     */
    public function __construct(CallbackDeferredProperty $deferred, $type)
    {
        $this->deferred = $deferred;
        $this->type     = $type;
    }

    /**
     * @return CallbackDeferredProperty
     */
    public function getDeferred()
    {
        return $this->deferred;
    }

    /**
     * @return array
     */
    public function getType()
    {
        return $this->type;
    }
}
