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

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineCustomSideload;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\InlineEntitySideload;
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
                'priority' => 16,
            ],
            [
                'event'    => Events::POST_SERIALIZE,
                'method'   => 'copyInlineEntitySideloads',
                'class'    => ApiWrapper::class,
                'format'   => 'json',
                'priority' => 64,
            ],

            [
                'event'    => Events::POST_SERIALIZE,
                'method'   => 'convertLinkedToObject',
                'class'    => ApiWrapper::class,
                'format'   => 'json',
                'priority' => 128,
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
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }

        $sideloads = $context->getSideloadStore();
        $includes  = $context->getIncludes();

        $sideloads->setInterests($includes);
        $context->setExclusionEnabled(false);

        $linked = [];

        // disable inline sideloading for linked objects
        $inlineSideloads = $context->isInlineSideloads();
        $context->setInlineSideloads(false);

        while ($includes && $sideloads->hasSideloads()) {
            foreach ($includes as $include) {
                $fqcn = $sideloads->getFqcn($include);
                if ($fqcn) {
                    $idsToLoad = $sideloads->getSideloads($include);
                    foreach ($this->em->getRepository($fqcn)->findBy(['id' => $idsToLoad]) as $entity) {
                        /* @var EntityInterface|DomainObject $entity */
                        $linked[$include][$entity->getId()] = $context->accept($entity);
                    }
                }
                if ($sideloads->hasCustom($include)) {
                    foreach ($sideloads->getCustom($include) as $custom) {
                        $linked[$include][$custom->getId()] = $context->accept($custom->getData());
                    }
                }
            }
        }

        // perhaps it's not necessary at all, but who knows where context will be used?
        $context->setExclusionEnabled(true);
        // restore original 'inline_sideloading' option
        $context->setInlineSideloads($inlineSideloads);

        $visitor->setData('linked', $linked);
    }

    /**
     * @param ObjectEvent $event
     */
    public function copyInlineEntitySideloads(ObjectEvent $event)
    {
        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }

        $data = VisitorDataAccessor::getData($visitor);
        if (isset($data['data']) && isset($data['linked']) && is_array($data['data'])) {
            $data['data']   = $this->recursiveCopyInlineEntitySideloads($data['data'], $data['linked'], $context);
            $data['linked'] = $this->removeInlineSideloads($data['linked']);

            VisitorDataAccessor::setData($visitor, $data);
        }
    }

    /**
     * @param ObjectEvent $event
     */
    public function convertLinkedToObject(ObjectEvent $event)
    {
        /** @var GenericSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }

        $data = VisitorDataAccessor::getData($visitor);
        if (isset($data['linked'])) {
            $data['linked'] = new \ArrayObject($data['linked']);

            VisitorDataAccessor::setData($visitor, $data);
        }
    }

    /**
     * @param array                        $data
     * @param array                        $linked
     * @param SideloadSerializationContext $context
     *
     * @return array
     */
    private function recursiveCopyInlineEntitySideloads(array $data, array $linked, $context)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof InlineCustomSideload) {
                if ($context->isInlineSideloads() && isset($linked[$value->getType()][$value->getId()])) {
                    $data[$key] = $linked[$value->getType()][$value->getId()];
                } else {
                    unset($data[$key]);
                }
            } elseif ($value instanceof InlineEntitySideload) {
                if (isset($linked[$value->getType()][$value->getId()])) {
                    $data[$key] = $linked[$value->getType()][$value->getId()];
                } else {
                    $data[$key] = $value->getId();
                }
            } elseif (is_array($value)) {
                $data[$key] = $this->recursiveCopyInlineEntitySideloads($value, $linked, $context);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function removeInlineSideloads($data)
    {
        foreach ($data as $key => $value) {
            if ($value instanceof InlineCustomSideload || $value instanceof InlineEntitySideload) {
                unset($data[$key]);
            } elseif (is_array($value)) {
                $data[$key] = $this->removeInlineSideloads($value);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
