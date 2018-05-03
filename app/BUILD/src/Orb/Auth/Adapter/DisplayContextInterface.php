<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * Adapters that behave differently depending on how they are used can
 * implmenet this interface.
 */
interface DisplayContextInterface extends AdapterInterface
{
    /**
     * @param mixed $context Context information
     */
    public function setDisplayContext($context);
}
