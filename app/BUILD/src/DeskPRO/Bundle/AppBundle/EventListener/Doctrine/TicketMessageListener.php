<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TicketMessageListener implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postLoad',
        ];
    }

    /**
     * @internal
     *
     * @param LifecycleEventArgs $args
     */
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

    /**
     * @param TicketMessage $ticketMessage
     * @param EntityManager $em
     *
     * @return array
     */
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

        $emailAccounts = $em->getRepository(EmailAccount::class)->findAll();
        $recipients    = array_filter($recipients, function ($recipient) use ($emailAccounts) {
            foreach ($emailAccounts as $emailAccount) {
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

        // We won't display anything if there's only one recipient
        if (count($recipients) <= 0 && count($participants) <= 1) {
            return [];
        }

        $result = [];
        /** @var Person $participant */
        foreach ($participants as $participant) {
            if (!array_intersect($participant->getEmailAddresses(false, false), $recipients)
                    && !array_intersect($participant->getEmailAddresses(false, false), $ticketMessage->getPerson()->getEmailAddresses(false, false))
            ) {
                $result['absent'][] = $participant;
            }
        }

        foreach ($recipients as $recipient) {
            /** @var PersonEmail $personEmail */
            $personEmail = $em->getRepository(PersonEmail::class)->findOneBy(['email' => $recipient]);
            if ($personEmail) {
                if ($personEmail->getPerson()->isAgent()) {
                    continue;
                }

                $result['cc'][] = $personEmail;
            } else {
                $result['cc'][] = $recipient;
            }
        }

        return $result;
    }
}
