<?php

/**
 * DeskPRO.
 */

namespace Orb\Serializer;

class SerializerRegistry implements SerializerInterface
{
    /**
     * @var SerializerInterface[]
     */
    private $serializers;

    /**
     * @param array $serializers start the registry off with some serializers
     */
    public function __construct(array $serializers = [])
    {
        foreach ($serializers as $serializer) {
            $this->addSerializer($serializer);
        }
    }

    /**
     * @param mixed  $data   anything that the serializer can handle
     * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
     * @param string $format requested return format - defaults to an array
     *
     * @throws \InvalidArgumentException if the passed data isn't supported
     *
     * @return mixed
     */
    public function serialize($data, $view = 'default', $format = 'array')
    {
        foreach ($this->serializers as $serializer) {
            if ($serializer->supports($data, $view, $format)) {
                return $serializer->serialize($data, $view, $format);
            }
        }

        throw new \InvalidArgumentException('cannot serialize data');
    }

    /**
     * @param mixed  $data   anything that the serializer can handle
     * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
     * @param string $format requested return format - defaults to an array
     *
     * @return mixed
     */
    public function supports($data, $view = 'default', $format = 'array')
    {
        foreach ($this->serializers as $serializer) {
            if ($serializer->supports($data, $view, $format)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a serializer.
     *
     * @param SerializerInterface $serializer
     */
    public function addSerializer(SerializerInterface $serializer)
    {
        $this->serializers[] = $serializer;
    }
}
