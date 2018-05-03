<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;

class NoopConfig implements AccountConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();

        return $obj;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'noop';
    }
}
