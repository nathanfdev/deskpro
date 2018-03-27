<?php

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
