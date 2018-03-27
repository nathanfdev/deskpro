<?php

/**
 * DeskPRO.
 */

namespace Orb\Serializer\Serializer;

use Orb\Serializer\SerializerInterface;

/**
 * Simplest possible serializer. If an array is passed, we return it back.
 */
class ArraySerializer implements SerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, $view = 'default', $format = 'array')
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, $view = 'default', $format = 'array')
    {
        return is_array($data) && $format === 'array';
    }
}
