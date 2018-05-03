<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Sideload;

use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;

/**
 * Class CustomSideload.
 */
class CustomSideload
{
    /**
     * @var
     */
    private $id;

    /**
     * @var CallbackDeferredProperty
     */
    private $deferredCallback;

    /**
     * CustomSideload constructor.
     *
     * @param                          $id
     * @param CallbackDeferredProperty $deferredCallback
     */
    public function __construct($id, CallbackDeferredProperty $deferredCallback)
    {
        $this->id               = $id;
        $this->deferredCallback = $deferredCallback;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CallbackDeferredProperty
     */
    public function getData()
    {
        return $this->deferredCallback->call();
    }
}
