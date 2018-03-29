<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;

class PhpMailConfig implements AccountConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function serializeJsonArray()
    {
        return ['PhpMail' => true];
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
        return 'php_mail';
    }
}
