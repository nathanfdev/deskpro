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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;

/**
 * Class SideloadListener.
 */
class SideloadListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $linked = [];

    /**
     * SideloadListener constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'    => Events::POST_SERIALIZE,
                'method'   => 'sideload',
                'class'    => ApiWrapper::class,
                'format'   => 'json',
                'priority' => 32,
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function sideload(ObjectEvent $event)
    {
        $this->linked = []; // just clear it

        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }

        $sideloads = $context->getSideloadStore();
        $includes  = $context->getIncludes();

        $sideloads->setInterests($includes);
        $context->setExclusionEnabled(false);

        while ($includes && $sideloads->hasSideloads()) {
            foreach ($includes as $include) {
                $fqcn = $sideloads->getFqcn($include);
                if ($fqcn) {
                    $ids_to_load = $sideloads->getSideloads($include);
                    foreach ($this->em->getRepository($fqcn)->findBy(['id' => $ids_to_load]) as $entity) {
                        /* @var EntityInterface|DomainObject $entity */
                        $this->addLinked($include, $entity->getId(), $context->accept($entity));
                    }
                }
                if ($sideloads->hasCustom($include)) {
                    foreach ($sideloads->getCustom($include) as $custom) {
                        $this->addLinked($include, $custom->getId(), $context->accept($custom->getData()));
                    }
                }
            }
        }

        // perhaps it's not necessary at all, but who knows where context will be used?
        $context->setExclusionEnabled(true);

        $visitor->addData('linked', $this->linked);
    }

    /**
     * @param $include
     * @param $id
     * @param $data
     */
    private function addLinked($include, $id, $data)
    {
        if (!isset($this->linked[$include])) {
            $this->linked[$include] = [];
        }

        $this->linked[$include][$id] = $data;
    }
}
