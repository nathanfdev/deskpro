<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketLayout;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * Class TicketLayoutListener.
 */
class TicketLayoutListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => Events::PRE_SERIALIZE,
                'method' => 'onSideloadJs',
                'class'  => TicketLayout::class,
            ],
        ];
    }

    /**
     * @internal
     *
     * @param ObjectEvent $event
     */
    public function onSideloadJs(ObjectEvent $event)
    {
        /** @var TicketLayout $model */
        $model   = $event->getObject();
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }

        if ($context->hasInclude('layout_js')) {
            $context->getSideloadStore()->addCustomSideload(
                'layout_js',
                $model->getDepartmentId(),
                new CallbackDeferredProperty([$this, 'getLayoutJs'], [$model])
            );
        }
    }

    /**
     * @internal
     *
     * @param TicketLayout $model
     *
     * @return string
     */
    public function getLayoutJs(TicketLayout $model)
    {
        return $model->getLayout()->compileJsObj();
    }
}
