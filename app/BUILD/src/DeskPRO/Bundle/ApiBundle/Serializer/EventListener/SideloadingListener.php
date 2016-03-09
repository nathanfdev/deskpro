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

namespace DeskPRO\Bundle\ApiBundle\Serializer\EventListener;

use DeskPRO\Bundle\ApiBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\ApiBundle\Serializer\SideloadStore;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\Serializer;

/**
 * Class SideloadingListener.
 */
class SideloadingListener implements EventSubscriberInterface
{
    /**
     * @var SideloadStore
     */
    protected $store;

    /**
     * @var Serializer
     */
    protected $serializer;

    protected $em;

    /**
     * SideloadingListener constructor.
     *
     * @param SideloadStore $store
     * @param Serializer    $serializer
     * @param EntityManager $em
     */
    public function __construct(SideloadStore $store, Serializer $serializer, EntityManager $em)
    {
        $this->store      = $store;
        $this->serializer = $serializer;
        $this->em         = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => Events::POST_SERIALIZE,
                'method' => 'sideload',
                'class'  => ApiWrapper::class,
                'format' => 'json',
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function sideload(ObjectEvent $event)
    {
        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $includes = ['language'];
        $linked   = [];
        foreach ($includes as $include) {
            if (!isset($linked[$include])) {
                $linked[$include] = [];
            }
            $fqcn        = $this->store->getFqcn($include);
            $ids_to_load = $this->store->getSideloads($fqcn);
            foreach ($this->em->getRepository($fqcn)->findBy(['id' => $ids_to_load]) as $entity) {
                $linked[$include][] = $event->getVisitor()->getNavigator()->accept($entity, null, $event->getContext());
            }
        }

        $visitor->addData('linked', $linked);
    }
}
