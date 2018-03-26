<?php

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
