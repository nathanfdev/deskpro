<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\OutgoingAccount;

class Office365ExchangeConfig extends ExchangeConfig
{
    /**
     * {@inheritdoc}
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        foreach ($data as $k => $v) {
            $obj->$k = $v;
        }

        return $obj;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'office365_exchange';
    }
}
