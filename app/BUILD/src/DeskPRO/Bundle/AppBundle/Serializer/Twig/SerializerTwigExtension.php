<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Twig;

use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class SerializerTwigExtension.
 */
class SerializerTwigExtension extends \Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'serializer_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('serializerContext', [$this, 'getSerializerContext']),
        ];
    }

    /**
     * @param array $groups
     *
     * @return SideloadSerializationContext
     */
    public function getSerializerContext(array $groups = [])
    {
        $context = new SideloadSerializationContext();
        if ($groups) {
            $context->setGroups($groups);
        }

        return $context;
    }
}
