<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataSerializer\EventListener;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializerEvent;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializerEvents;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTypeMap;
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
            DataSerializerEvents::PRE_SERIALIZE => ['preSerialize', 0],
        ];
    }

    /**
     * Use the DataTypeMap to find the type of data we are dealing with. Set it to the context.
     *
     * @param DataSerializerEvent $event
     *
     * @throws \Exception
     */
    public function preSerialize(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        $type = $context->getMainType();
        $data = $context->getMainData();

        if (null === $type) {
            // try to find the type one last time before finally serializing
            $type = $this->type_map->findType($data);

            $context->setMainType($type);
        }
    }
}
