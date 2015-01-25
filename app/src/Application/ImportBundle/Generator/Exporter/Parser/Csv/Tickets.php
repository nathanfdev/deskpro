<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * Tickets csv file parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
class Tickets extends AbstractParser
{
    const FILE_TICKETS         = 'tickets.csv';
    const FILE_TICKET_MESSAGES = 'messages.csv';

    /**
     * {@inheritdoc}
     */
    public function getRecordType()
    {
        return GeneratorInterface::RECORD_TYPE_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getRowsCount($this->getTicketsConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $tickets    = $this->reader->getData($this->getTicketsConfig());
        $messages   = $this->exportMessages();

        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();

            if ($this->hasRequiredTicketColumns($ticket) === false) {
                $this->logWarning(sprintf('Invalid ticket record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\Ticket();
                $entity
                    ->setDestination('ticket_' . trim($ticket['id']))
                    ->setRef($ticket['id'])
                    ->setSubject($ticket['subject'])
                    ->setPersonEmail($ticket['user'])
                    ->setAgentEmail(isset($ticket['agent']) ? $ticket['agent'] : null)
                    ->setStatus(isset($ticket['status']) ? $ticket['status'] : DeskPROEntity\Ticket::STATUS_AWAITING_AGENT)
                    ->setDateCreated(isset($ticket['date_created']) ? new DateTime($ticket['date_created']) : new DateTime());

                foreach ($messages as $message_entity) {
                    /** @var Entity\TicketMessage $message_entity */
                    if ($entity->getDestination() === $message_entity->getDestination()) {
                        $entity->addMessage($message_entity);
                    }
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of ticket messages
     *
     * @return Entity\Collection
     */
    private function exportMessages()
    {
        $collection = new Entity\Collection();
        $messages   = $this->reader->getData($this->getTiketMessagesConfig());

        foreach ($messages as $num => $message) {
            if ($this->hasRequiredTicketMessageColumns($message) === false) {
                $this->logWarning(sprintf('Invalid ticket message record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\TicketMessage();
                $entity
                    ->setDestination('ticket_' . trim($message['ticket_id']))
                    ->setPersonEmail($message['user'])
                    ->setMessageText($message['message_text'])
                    ->setDateCreated(isset($message['date_created']) ? new DateTime($message['date_created']) : new DateTime());

                $collection->attach($entity);
            }
        }

        return $collection;
    }

    /**
     * Check if ticket has all required columns
     *
     * @param array $ticket
     * @return bool
     */
    private function hasRequiredTicketColumns(array $ticket)
    {
        return $this->hasRequiredColumns($ticket, array('id', 'subject', 'user'));
    }

    /**
     * Check if ticket message has all required columns
     *
     * @param array $message
     * @return bool
     */
    private function hasRequiredTicketMessageColumns(array $message)
    {
        return $this->hasRequiredColumns($message, array('ticket_id', 'message_text', 'user'));
    }

    /**
     * Returns reader of ticket records config
     *
     * @return \Application\ImportBundle\CsvReader\CsvConfig
     */
    private function getTicketsConfig()
    {
        return $this->getReaderConfig(self::FILE_TICKETS);
    }

    /**
     * Returns reader of ticket message records config
     *
     * @return \Application\ImportBundle\CsvReader\CsvConfig
     */
    private function getTiketMessagesConfig()
    {
        return $this->getReaderConfig(self::FILE_TICKET_MESSAGES);
    }
}
