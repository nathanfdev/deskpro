<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\StateHandler;

/**
 * A state handler saves auth data between requests. This is mostly only needed
 * for adapters that require a two-step login process such as OpenID.
 */
interface StateHandlerInterface extends \ArrayAccess
{
    /**
     * Clears all state data, or resets back into its initial state.
     */
    public function clearState();
}
