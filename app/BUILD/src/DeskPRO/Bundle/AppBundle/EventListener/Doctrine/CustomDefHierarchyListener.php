<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Class CustomDefHierarchyListener.
 */
class CustomDefHierarchyListener implements EventSubscriber
{
    /**
     * @var array
     */
    private $hierarchyMapping = [];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
            'postFlush',
            'preRemove',
        ];
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->manageLazyHierarchy($args);
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->manageLazyHierarchy($args);
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof CustomDefAbstract) {
            return;
        }

        $parent = $entity->getParent();

        // handle orphaned sub choices
        if ($parent) {
            foreach ($parent->getChildren() as $child) {
                if ($child->getOption('parent_id') === $entity->getId()) {
                    $args->getEntityManager()->remove($child);
                }
            }
        }
    }

    /**
     * @internal
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        foreach ($this->hierarchyMapping as $defHash => $defMapping) {
            /** @var CustomDefAbstract $parent */
            /** @var CustomDefAbstract[] $children */
            $parent   = $defMapping['parent'];
            $children = $defMapping['children'];

            foreach ($children as $child) {
                $child->setOption('parent_id', $parent->getId());
                $args->getEntityManager()->persist($child);
            }

            unset($this->hierarchyMapping[$defHash]);

            $args->getEntityManager()->flush();
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function manageLazyHierarchy(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof CustomDefAbstract) {
            return;
        }

        $parent = $entity->getOption('parent_id');
        if (!$parent || !$parent instanceof CustomDefAbstract) {
            return;
        }

        if ($parent->getId()) {
            // parent def already has id, overwrite ref with real value
            $entity->setOption('parent_id', $parent->getId());
        } else {
            // collect mapping to set id after persistent
            $parentHash = spl_object_hash($parent);
            $entityHash = spl_object_hash($entity);

            if (!isset($this->hierarchyMapping[$parentHash][$entityHash])) {
                $this->hierarchyMapping[$parentHash]['children'][$entityHash] = $entity;
                $this->hierarchyMapping[$parentHash]['parent']                = $parent;
            }

            $entity->setOption('parent_id', null);
        }
    }
}
