<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler;

/**
 * Class SerializerTypes.
 */
final class SerializerTypes
{
    const TYPE_RAW             = 'raw';
    const TYPE_ENTITY          = 'entity';
    const TYPE_TO_STRING       = 'to_string';
    const TYPE_CUSTOM_DATA     = 'custom_data';
    const TYPE_CUSTOM_PER_DATA = 'custom_per_data';
    const TYPE_COLLECTION      = 'collection';
    const TYPE_MAP             = 'map';
    const TYPE_DEFERRED        = 'deferred';
    const TYPE_LABEL           = 'label';
}
