<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\StateHandler\StateHandlerInterface;

/**
 * Adapters that use a two-step authentication scheme with a callback (such as OpenID)
 * often need to store state information and this interface should be used.
 */
interface SessionStateInterface extends AdapterInterface
{
    /**
     * Sets the state handler.
     *
     * @param Orb\Auth\StateHandler\StateHandlerInterface $state The state handler
     */
    public function setStateHandler(StateHandlerInterface $state);
}
