<?php

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

        $collector->clear();
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
