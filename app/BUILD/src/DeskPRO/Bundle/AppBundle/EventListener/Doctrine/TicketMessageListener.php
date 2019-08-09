<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\LazyCriteriaCollection;

class TicketMessageListener implements EventSubscriber
{
    /**
     * @var ArrayCollection
     */
    private $accounts = null;

    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof TicketMessage) {
            return;
        }

        /* @var TicketMessage $entity */
        $entity->setEmailAccounts($this->getLazyCriteriaCollection($entity, $args->getEntityManager()));
    }

    private function getLazyCriteriaCollection(TicketMessage $entity, EntityManager $em)
    {
        if (!isset($this->accounts)) {
            $persister = $em->getUnitOfWork()->getEntityPersister(EmailAccount::class);
            $criteria  = new Criteria();

            $this->accounts = new LazyCriteriaCollection($persister, $criteria);
        }

        return $this->accounts;
    }
}
