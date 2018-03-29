<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder;

class PhpCheckSerializer
{
    /**
     * @param string $serialized_check the result of self::serialize()
     *
     * @return PhpCheck
     */
    public function unserialize($serialized_check)
    {
        // CN requested we silence this, because people tend to mess with cached vals in the DB
        return @unserialize($serialized_check);
    }

    /**
     * @param PhpCheck $check
     *
     * @return string the storable serialized version
     */
    public function serialize(PhpCheck $check)
    {
        return serialize($check);
    }
}
