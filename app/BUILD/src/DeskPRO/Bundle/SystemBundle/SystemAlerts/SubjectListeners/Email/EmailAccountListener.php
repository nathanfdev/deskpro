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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\SubjectListeners\Email;

use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\IncomingEmailFailureIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\OutgoingEmailFailureIncident;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmailAccountListener.
 */
class EmailAccountListener
{
    /**
     * @var array Array of event and incident classes to be deleted if their subject gets changes or removed
     */
    private static $targets = [
        IncomingEmailFailureIncident::class,
        OutgoingEmailFailureIncident::class,
        IncomingEmailFailureEvent::class,
        IncomingEmailSuccessEvent::class,
        OutgoingEmailFailureEvent::class,
        OutgoingEmailSuccessEvent::class,
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Removes events and incidents if email address has been changed.
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        if (($object = $args->getObject()) instanceof EmailAccount && $args->hasChangedField('address')) {
            $this->cleanTargets($object);
        }
    }

    /**
     * Removes related events and incidents.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        if (($object = $args->getObject()) instanceof EmailAccount) {
            $this->cleanTargets($object);
        }
    }

    /**
     * @param EmailAccount $email
     */
    private function cleanTargets(EmailAccount $email)
    {
        foreach (self::$targets as $target) {
            $this
                ->getSystemEntityManager()
                ->createQuery("DELETE $target t WHERE t.subjectUniqueId = ?0")
                ->execute([$email->getId()]);
        }
    }

    /**
     * @return EntityManager
     */
    private function getSystemEntityManager()
    {
        return $this->container->get('doctrine.orm.system_entity_manager');
    }
}
