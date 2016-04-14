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

use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class SideloadListener.
 */
class SideloadListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

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
        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        /** @var SideloadSerializationContext $context */
        $context   = $event->getContext();
        $sideloads = $context->getSideloadStore();
        $includes  = $context->getIncludes();

        $linked = [];
        $sideloads->setInterests($includes);

        $context->setExclusionEnabled(false);
        while ($includes && $sideloads->hasSideloads()) {
            foreach ($includes as $include) {
                $fqcn = $sideloads->getFqcn($include);
                if ($fqcn) {
                    $ids_to_load = $sideloads->getSideloads($include);
                    foreach ($this->em->getRepository($fqcn)->findBy(['id' => $ids_to_load]) as $entity) {
                        $this->addLinked($linked, $include, $entity->getId(), $context->accept($entity));
                    }
                }
                if ($sideloads->hasCustom($include)) {
                    foreach ($sideloads->getCustom($include) as $custom) {
                        $this->addLinked($linked, $include, $custom->getId(), $context->accept($custom->getData()));
                    }
                }
            }
        }

        // unfortunately we could realise that provided includes contain error only after we finish all sideloads
        // e.g. we loading (note) -> sideload (person) -> sideload (organization) -> sideload (ticket)
        // so while sideloading person we can realise that ticket could be sideloaded, until step with organization
        $availableTypes = $sideloads->getAvailableTypes();

        if (count($diff = array_diff($includes, $availableTypes)) > 0) {
            throw new BadRequestHttpException(
                sprintf(
                    'You can\'t sideload [ %s ], available types for sideloading are [ %s ]',
                    implode(', ', $diff),
                    implode(', ', $availableTypes)
                )
            );
        }

        // perhaps it's not necessary at all, but who knows where context will be used?
        $context->setExclusionEnabled(true);

        $visitor->addData('linked', $linked);
    }

    protected function addLinked(&$linked, $include, $id, $data)
    {
        if (!isset($linked[$include])) {
            $linked[$include] = [];
        }

        $linked[$include][$id] = $data;
    }
}
