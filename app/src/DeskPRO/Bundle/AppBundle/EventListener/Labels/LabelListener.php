<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\EventListener\Labels;

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
        $def = $this->findDef($em, $label);
        $def->decrement();
        if ($def->getTotal() > 0) {
            $em->createQuery('UPDATE DeskPRO:LabelDef d SET d.total = ?0 WHERE d.label_type = ?1 AND d.label = ?2')
                ->setParameters([$def->getTotal(), $label->getType(), $label->getLabel()])
                ->execute();
        } else {
            $em->createQuery('DELETE FROM DeskPRO:LabelDef d WHERE d.label_type = ?0 AND d.label = ?1')
                ->setParameters([$label->getType(), $label->getLabel()])
                ->execute();
        }
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
