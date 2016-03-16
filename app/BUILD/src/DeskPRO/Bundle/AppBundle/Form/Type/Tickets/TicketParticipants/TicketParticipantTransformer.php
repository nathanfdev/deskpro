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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class TicketParticipantTransformer.
 */
class TicketParticipantTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param Ticket        $ticket
     */
    public function __construct(EntityManager $em, Ticket $ticket)
    {
        $this->em     = $em;
        $this->ticket = $ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value ? $value->getPersonEmail()->getEmail() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$this->ticket) {
            throw new \InvalidArgumentException('Ticket is not defined.');
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $person_repo */
        $person_repo  = $this->em->getRepository(Person::class);
        $participants = $this->ticket->getParticipants();

        $filtered = $participants->filter(function (TicketParticipant $participant) use ($value) {
            return $participant->getPersonEmail()->getEmail() === $value;
        });

        if (count($filtered) > 0) {
            $entity = $filtered->first();
        } else {
            $person = $person_repo->findOneByEmail($value);
            if (!$person) {
                throw new TransformationFailedException('Person with email `'.$value.'` not found');
            }

            $person_email = $person->getEmails()->filter(function (PersonEmail $person_email) use ($value) {
                return $person_email->getEmail() === $value;
            })->first();

            $entity = new TicketParticipant();
            $entity
                ->setPerson($person)
                ->setPersonEmail($person_email)
                ->setTicket($this->ticket)
            ;
        }

        return $entity;
    }
}
