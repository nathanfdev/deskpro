<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

/**
 * Interface EntityHandlerInterface.
 */
interface EntityHandlerInterface
{
    /**
     * @return string|string[]
     */
    public static function getClassNames();
}
