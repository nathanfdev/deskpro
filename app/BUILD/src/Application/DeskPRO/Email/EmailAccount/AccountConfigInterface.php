<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount;

use Orb\Types\JsonObjectSerializable;

interface AccountConfigInterface extends JsonObjectSerializable
{
    /**
     * The type of account the config represents. Should be understandable by the various factories.
     *
     * @return string
     */
    public function getType();
}
