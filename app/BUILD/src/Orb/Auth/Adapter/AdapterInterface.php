<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Result;

interface AdapterInterface
{
    /**
     * Authenticate a user.
     *
     * @return Result
     */
    public function authenticate();
}
