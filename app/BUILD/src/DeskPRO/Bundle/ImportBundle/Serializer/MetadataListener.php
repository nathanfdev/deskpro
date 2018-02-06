<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ImportBundle\Serializer;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use Orb\Util\Strings;

/**
 * Class MetadataListener.
 */
class MetadataListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => Events::PRE_DESERIALIZE,
                'method' => 'onPreDeserialize',
            ],
        ];
    }

    /**
     * Check metadata to make sure we are not trying to process unknown props.
     *
     * @param PreDeserializeEvent $event
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        if (!is_array($event->getData())) {
            return;
        }

        $modelClass = $event->getType()['name'];

        // exposed for the import bundle models only
        if (strpos($modelClass, 'DeskPRO\Bundle\ImportBundle') !== 0) {
            return;
        }

        if (!isset($this->cache[$modelClass])) {
            $availableProps = [];
            $reflection     = new \ReflectionClass($modelClass);

            do {
                $properties = $reflection->getProperties();
                foreach ($properties as $property) {
                    $availableProps[] = Strings::camelCaseToUnderscore($property->getName());
                }
            } while ($reflection = $reflection->getParentClass());

            $this->cache[$modelClass] = $availableProps;
        }

        $rawProps       = array_keys($event->getData());
        $availableProps = $this->cache[$modelClass];

        $unknownProps = array_diff($rawProps, $availableProps);
        if ($unknownProps) {
            throw new \RuntimeException(
                "Unable to parse $modelClass, unknown model props given: ".implode(', ', $unknownProps).'. '.
                'Available props are: '.implode(', ', $availableProps)
            );
        }
    }
}
