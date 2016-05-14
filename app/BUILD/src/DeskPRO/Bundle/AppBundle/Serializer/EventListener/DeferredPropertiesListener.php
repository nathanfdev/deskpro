<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\WrappedDeferred;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;

/**
 * Class DeferredPropertiesListener.
 */
class DeferredPropertiesListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'    => Events::POST_SERIALIZE,
                'method'   => 'loadDeferred',
                'class'    => ApiWrapper::class,
                'format'   => 'json',
                'priority' => 16,
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function loadDeferred(ObjectEvent $event)
    {
        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }

        $data = VisitorDataAccessor::getData($visitor);
        if (is_array($data['data'])) {
            $data['data'] = $this->resolveArray($data['data'], $context);
            VisitorDataAccessor::setData($visitor, $data);
        }
    }

    /**
     * @param array                        $data
     * @param SideloadSerializationContext $context
     *
     * @return array
     */
    protected function resolveArray(array $data, SideloadSerializationContext $context)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof WrappedDeferred) {
                $data[$key] = $context->accept($value->getDeferred()->call(), $value->getType());
            } elseif (is_array($value)) {
                $data[$key] = $this->resolveArray($value, $context);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
