<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query;

/**
 * Class DbalQuerySerializer.
 */
class DbalQuerySerializer
{
    /**
     * @param string $serialized_compiled_query the result of self::serialize()
     *
     * @return DbalQuery
     */
    public function unserialize($serialized_compiled_query)
    {
        // CN requested we silence this, because people tend to mess with cached vals in the DB
        return @unserialize($serialized_compiled_query);
    }

    /**
     * @param DbalQuery $compiled_query
     *
     * @return string the storable serialized version
     */
    public function serialize(DbalQuery $compiled_query)
    {
        return serialize($compiled_query);
    }
}
