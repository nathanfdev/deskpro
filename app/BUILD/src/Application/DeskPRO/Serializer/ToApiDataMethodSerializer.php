<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Serializer;

use Orb\Serializer\SerializerInterface;

class ToApiDataMethodSerializer implements SerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, $view = 'default', $format = 'array')
    {
        // to use the various methods on these functions, you might need to create a new class just like this one
        // and register it in the system service file.
        // OR you could use this one, but have this one manage different "views" that call toApiData differently.
        return $data->toApiData();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, $view = 'default', $format = 'array')
    {
        return is_object($data) && method_exists($data, 'toApiData');
    }
}
