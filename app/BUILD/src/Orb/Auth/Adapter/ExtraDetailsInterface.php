<?php

/**
 * Orb.
 *
 * @category Auth
 */

namespace Orb\Auth\Adapter;

/**
 * An auth adater that can provide extra details unqiue to itself (saml metadata, callback urls, etc).
 */
interface ExtraDetailsInterface
{
    /**
     * @return array
     */
    public function getExtraDetails();
}
