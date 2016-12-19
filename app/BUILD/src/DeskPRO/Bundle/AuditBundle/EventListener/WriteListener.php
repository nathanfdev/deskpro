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

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AuditBundle\Event\LogEvent;
use DeskPRO\Bundle\AuditBundle\Log\AuditLogService;
use DeskPRO\Bundle\AuditBundle\Log\ObjectCollector;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WriteListener.
 */
class WriteListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * WriteListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param LogEvent $event
     */
    public function onFinish(LogEvent $event)
    {
        if ($event->shouldWrite()) {
            $this->getLogService()->write($event->getLog());
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $collector = $this->getObjectCollector();
        foreach ($collector->getOwners() as $type => $owner) {
            foreach ($owner as $id => $item) {
                /* @var LogEvent $item */
                $parts = $collector->getParts($item->getContext()->getEntity());
                $diff  = [];
                foreach ($parts as $part) {
                    /* @var LogEvent $part */
                    /** @var CustomDataAbstract $entity */
                    $entity    = $part->getContext()->getEntity();
                    $partLog   = $part->getLog();
                    $innerDiff = [];
                    if ($entity->root_field->getType() === CustomDefAbstract::TYPE_CHOICE) {
                        $innerDiff += [
                            'title'          => $entity->root_field->getTitle(),
                            'readable_value' => sprintf('%s(%d)', $entity->field->getTitle(), $entity->field->getId()),
                        ];
                    } else {
                        $innerDiff += [
                            'title' => $entity->field->getTitle(),
                        ];
                    }
                    $innerDiff += [
                        'action'      => $partLog->getAction(),
                        'actual_diff' => $partLog->getData()->getDiff(),
                        ];

                    $diff[] = $innerDiff;
                }
                $data = clone $item->getLog()->getData();
                $data->setDiff(array_merge($item->getLog()->getData()->getDiff(), ['custom_data' => $diff]));
                $data->setContext(['updated' => 'by_custom_data']);
                $item->getLog()->setData($data);
                $this->getLogService()->write($item->getLog());
            }
        }
    }

    /**
     * @return AuditLogService
     */
    private function getLogService()
    {
        return $this->container->get('audit_log.service');
    }

    /**
     * @return ObjectCollector
     */
    private function getObjectCollector()
    {
        return $this->container->get('audit_log.object_collector');
    }
}
