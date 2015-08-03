<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\EventListener;

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerContext;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerEvent;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerEvents;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTypeMap;
use DeskPRO\Bundle\ApiBundle\DataSerializer\Exception\DataSerializerException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Find and set the type of data in the context.
 */
class TypeListener implements EventSubscriberInterface
{
    /**
     * @var DataTypeMap
     */
    private $type_map;

    public function __construct(DataTypeMap $type_map)
    {
        $this->type_map = $type_map;
    }

    public static function getSubscribedEvents()
    {
        return [
            DataSerializerEvents::PRE_SERIALIZE => ['preSerialize', 0]
        ];
    }

    public function preSerialize(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        $type = $context->getMainType();
        $data = $context->getMainData();

        if (null === $type) {
            if (!($type = $this->type_map->findType($data)) && is_object($data)) {
                throw new DataSerializerException(
                    'could not find object type for given data, and no specific type provided'
                );
            }

            $context->setMainType($type);
        }
    }
}
