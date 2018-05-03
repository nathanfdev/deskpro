<?php

namespace DeskPRO\Bundle\AppBundle\Twig;

use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Override JMS serializer extensions because we need to use it with SideloadSerializationContext.
 */
class SerializerExtension extends \JMS\Serializer\Twig\SerializerExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('serialize', [$this, 'sideloadSerialize']),
        ];
    }

    /**
     * @param mixed $value
     * @param array $includes
     *
     * @return string
     */
    public function sideloadSerialize($value, array $includes = [])
    {
        $context = new SideloadSerializationContext($includes);

        return $this->serializer->serialize(new ApiWrapper($value), 'json', $context);
    }
}
