<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Serializer\PersonSerializer;
use Application\DeskPRO\Serializer\SerializerRegistry;
use Application\DeskPRO\Serializer\ToApiDataMethodSerializer;
use Orb\Serializer\Serializer\ArraySerializer;

class SerializerService
{
    public static function create(DeskproContainer $container)
    {
        /*
         * Recall that the ORDER matters in the registry. First added, first checked.
         * Put more specific serializers at the top, and more generic at the bottom.
         */
        $serializer = new SerializerRegistry();
        $serializer->addSerializer(new PersonSerializer($container->get('deskpro.core.settings')));
        $serializer->addSerializer(new ToApiDataMethodSerializer());
        $serializer->addSerializer(new ArraySerializer());

        return $serializer;
    }
}
