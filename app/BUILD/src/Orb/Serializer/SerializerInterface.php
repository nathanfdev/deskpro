<?php

/**
 * DeskPRO.
 */

namespace Orb\Serializer;

/**
 * A common interface used to serialize data.
 *
 * A serializer is used by giving it data, and the output view and format you are requesting.
 *
 * The support() method is called first, and if true, serialize will be called with the same data.
 */
interface SerializerInterface
{
    /**
     * @param mixed  $data   anything that the serializer can handle
     * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
     * @param string $format requested return format - defaults to an array
     *
     * @return mixed
     */
    public function serialize($data, $view = 'default', $format = 'array');

    /**
     * @param mixed  $data   anything that the serializer can handle
     * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
     * @param string $format requested return format - defaults to an array
     *
     * @return mixed
     */
    public function supports($data, $view = 'default', $format = 'array');
}
