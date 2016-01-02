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
namespace DeskPRO\Bundle\AppBundle\Ticket;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\DuplicateTicketException;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\PortalBundle\CustomField\Context\CustomPerFieldManager;
use Doctrine\ORM\EntityManager;

/**
 * Class NewTicket.
 */
class NewTicket
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketManager
     */
    private $ticket_manager;

    /**
     * @var CustomPerFieldManager
     */
    private $custom_per_field_manager;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TicketManager         $ticket_manager
     * @param CustomPerFieldManager $custom_per_field_manager
     */
    public function __construct(EntityManager $em, TicketManager $ticket_manager, CustomPerFieldManager $custom_per_field_manager)
    {
        $this->em                       = $em;
        $this->ticket_manager           = $ticket_manager;
        $this->custom_per_field_manager = $custom_per_field_manager;
    }

    /**
     * @param Ticket $ticket
     * @param Person $person
     *
     * @throws \Exception
     *
     * @return Ticket
     */
    public function saveNewTicket(Ticket $ticket, Person $person)
    {
        $this->em->beginTransaction();

        try {
            // allow all blobs for a new ticket
            foreach ($ticket->messages as $message) {
                foreach ($message->getAttachments() as $attachment) {
                    $blob = $attachment->getBlob();
                    if ($blob) {
                        $blob->is_temp = false;
                    }
                }
            }

            $this->em->persist($ticket);

            // we handle this the new way (TicketManager), so disable the doctrine auto ticket process
            $ticket->disableAutoTicketProcess();
            $context = $this->ticket_manager->createUserExecutorContext($person, 'newticket', 'portal');

            $this->ticket_manager->saveTicket($ticket, $context);
            $this->em->flush();
            $this->custom_per_field_manager->flushDataQueue();
            $this->em->commit();
        } catch (DuplicateTicketException $e) {
            $this->em->rollback();
            $ticket = $this->em->find('DeskPRO:Ticket', $e->ticket_id);

            return $ticket;
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $ticket;
    }
}
