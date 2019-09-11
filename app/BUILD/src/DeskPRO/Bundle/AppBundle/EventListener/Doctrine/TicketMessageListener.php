<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TicketMessageListener implements EventSubscriber
{
    /**
     * @var ArrayCollection
     */
    private $accounts = null;

    /**
     * @var array
     */
    private $collectionCache = [];

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

        $em = $args->getEntityManager();
        /* @var TicketMessage $entity */
        $entity->setEmailRecipients(function () use ($entity, $em) {
            return $this->getRecipients($entity, $em);
        });
    }

    public function getRecipients(TicketMessage $ticketMessage, EntityManager $em)
    {
        if ($ticketMessage->isAgentNote()) {
            return [];
        }
        $recipients = [];
        $keyArray   = [];
        $attribute  = $ticketMessage->getAttribute('email_recipients');
        if (!$attribute) {
            return [];
        }
        $value = json_decode($attribute->getValue());
        if ($value) {
            foreach ($value as $recipient) {
                if (!in_array($recipient->address, $keyArray)) {
                    $keyArray[]   = $recipient->address;
                    $recipients[] = $recipient->address;
                }
            }
        }
        $recipients = array_filter($recipients, function ($recipient) use ($em) {
            foreach ($em->getRepository(EmailAccount::class)->findAll() as $emailAccount) {
                if ($recipient === $emailAccount->address) {
                    return false;
                }
            }

            return true;
        });
        $ticket         = $ticketMessage->getTicket();
        $participants[] = $ticket->getPerson();

        foreach ($ticket->getParticipants() as $participant) {
            if (!$participant->getPerson()->isAgent()) {
                $participants[] = $participant->getPerson();
            }
        }
        // We won't display anything if there's only on recipient
        if (count($recipients) <= 0 && count($participants) <= 1) {
            return [];
        }

        $result = [];
        /** @var Person $participant */
        foreach ($participants as $participant) {
            if (in_array($participant->getEmailAddress(), $recipients)) {
                $result['cc'][] = $participant;
            } else {
                if ($participant->getEmailAddress() !== $ticketMessage->getPerson()->getEmailAddress()) {
                    $result['absent'][] = $participant;
                }
            }
        }

        foreach ($recipients as $recipient) {
            $present = false;
            if (!empty($result['cc'])) {
                foreach ($result['cc'] as $cc) {
                    if ($cc->getEmailAddress() === $recipient) {
                        $present = true;
                        break 1;
                    }
                }
            }
            if (!$present) {
                $person         = $em->getRepository(Person::class)->findOneByEmail($recipient);
                $result['cc'][] = $person ? $person : $recipient;
            }
        }

        return $result;
    }
}
