<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Serializer\EventListener;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Util\TermTypeCodes;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * JMS Handler.
 */
class TermEngineTermSubscriber implements EventSubscriberInterface
{
    public function onPostSerialize(
        ObjectEvent $event
    ) {
        $obj     = $event->getObject();
        $visitor = $event->getVisitor();

        if ($obj instanceof TermInterface && $visitor instanceof JsonSerializationVisitor) {
            $type = TermTypeCodes::getTermTypeCode($obj);
            $visitor->addData('type', $type);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
            ],
        ];
    }
}
