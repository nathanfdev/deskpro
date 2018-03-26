<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\Labels\Label;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class LabelListener.
 *
 * Listens for Label entities lifecycle callbacks and performs needed actions on corresponding LabelDef
 */
class LabelListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var $label Label */
        if (!($label = $args->getEntity()) instanceof Label) {
            return;
        }

        $em  = $args->getEntityManager();
        $def = $this->findDef($em, $label) ?: $this->createDefFor($label);
        $def->increment();
        $em->persist($def);
        $em->flush();
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        /** @var $label Label */
        if (!($label = $args->getEntity()) instanceof Label) {
            return;
        }

        $em  = $args->getEntityManager();
        $def = $this->findDef($em, $label) ?: $this->createDefFor($label);
        $def->decrement();
        $em
            ->createQuery('UPDATE DeskPRO:LabelDef d SET d.total = ?0 WHERE d.label_type = ?1 AND d.label = ?2')
            ->setParameters([$def->getTotal(), $label->getType(), $label->getLabel()])
            ->execute();
    }

    /**
     * @param EntityManager $em
     * @param Label         $label
     *
     * @return null|object
     */
    private function findDef(EntityManager $em, Label $label)
    {
        return $em->getRepository(LabelDef::class)->findOneBy([
            'label_type' => $label->getType(),
            'label'      => $label->getLabel(),
        ]);
    }

    /**
     * @param Label $label
     *
     * @return LabelDef
     */
    private function createDefFor(Label $label)
    {
        $def = new LabelDef([
            'color'      => LabelDef::DEFAULT_COLOR,
            'label_type' => $label->getType(),
            'label'      => $label->getLabel(),
        ]);

        return $def;
    }
}
