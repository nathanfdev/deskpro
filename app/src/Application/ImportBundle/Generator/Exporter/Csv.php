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

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\ImportBundle\Generator\Exporter;

use Application\ImportBundle\CsvReader\CsvConfig;
use Application\ImportBundle\CsvReader\CsvReaderInterface;
use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Entity;
use DateTime;
use Exception;

/**
 * Data generator from csv files
 *
 * Class Csv
 * @author Abhinav Kumar <abhinav.kumar@deskpro.com>
 * @package Application\ImportBundle\Generator\Exporter
 */
final class Csv extends AbstractExporter implements ExporterInterface
{
    const FILE_PEOPLE          = 'people.csv';
    const FILE_TICKETS         = 'tickets.csv';
    const FILE_TICKET_MESSAGES = 'messages.csv';

    /**
     * @var CsvReaderInterface
     */
    private $csv_reader;

    /**
     * Constructor
     *
     * @param CsvReaderInterface $csv_reader
     */
    public function __construct(CsvReaderInterface $csv_reader)
    {
        $this->csv_reader = $csv_reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_CSV;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordsCountByType($type)
    {
        switch ($type) {
            case GeneratorInterface::RECORD_TYPE_PEOPLE:
                return $this->csv_reader->getRowsCount($this->getCsvReaderConfig(self::FILE_PEOPLE));
            case GeneratorInterface::RECORD_TYPE_TICKETS:
                return $this->csv_reader->getRowsCount($this->getCsvReaderConfig(self::FILE_TICKETS));
            default:
                throw new Exception('This record type `%s` is not supported', $type);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exportRecordsByType($type)
    {
        switch ($type) {
            case GeneratorInterface::RECORD_TYPE_PEOPLE:
                return $this->exportPeople();
            case GeneratorInterface::RECORD_TYPE_TICKETS:
                return $this->exportTickets();
            default:
                throw new Exception('This record type `%s` is not supported', $type);
        }
    }

    /**
     * Returns exporting raw data
     *
     * @param string $type
     * @return array
     */
    private function getDataByRecordType($type)
    {
        return $this->csv_reader->getData($this->getCsvReaderConfig($type));
    }

    /**
     * Get absolute file path
     *
     * @param string $record_type
     * @return CsvConfig
     */
    private function getCsvReaderConfig($record_type)
    {
        return new CsvConfig(sprintf('%s/%s', $this->config->getInputPath(), $record_type));
    }

    /**
     * Returns people collection
     *
     * @return Entity\Collection
     */
    private function exportPeople()
    {
        $collection = new Entity\Collection();
        $people = $this->getDataByRecordType(self::FILE_PEOPLE);
        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            if ($this->hasRequiredPersonColumns($person) === false) {
                $this->logWarning(sprintf('Invalid person record found (Skipping): %d', $num));
            } else {
                if (empty($person['name'])) {
                    $e = explode('@', $person['email'], 2);
                    $person['name'] = $e[0];
                }

                $names  = explode(' ', $person['name']);
                $entity = new Entity\Person();
                $entity
                    ->setDestination('person_' . $num)
                    ->setOid($num)
                    ->setAsAgent(isset($person['is_agent']) ? (bool)$person['is_agent'] : false)
                    ->setName($person['name'])
                    ->setFirstName($names[0])
                    ->setLastName(isset($names[1]) ? $names[1] : '')
                    ->setDateCreated(new DateTime())
                    ->addEmail($person['email']);

                $collection->attach($entity);
                $this->logInfo(sprintf('%s exported successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of tickets
     *
     * @return Entity\Collection
     */
    private function exportTickets()
    {
        $collection = new Entity\Collection();
        $tickets    = $this->getDataByRecordType(self::FILE_TICKETS);
        $messages   = $this->exportTicketMessages();

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
                    ->setPerson($ticket['user'])
                    ->setAgent(isset($ticket['agent']) ? $ticket['agent'] : null)
                    ->setStatus(isset($ticket['status']) ? $ticket['status'] : Entity\Ticket::STATUS_AWAITING_AGENT)
                    ->setDateCreated(isset($ticket['date_created']) ? new DateTime($ticket['date_created']) : new DateTime());

                foreach ($messages as $message_entity) {
                    /** @var Entity\TicketMessage $message_entity */
                    if ($entity->getDestination() === $message_entity->getDestination()) {
                        $entity->addMessage($message_entity);
                    }
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('%s exported successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of ticket messages
     *
     * @return Entity\Collection
     */
    private function exportTicketMessages()
    {
        $collection = new Entity\Collection();
        $messages   = $this->getDataByRecordType(self::FILE_TICKET_MESSAGES);

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
     * Check if person has all required columns
     *
     * @param array $person
     * @return bool
     */
    private function hasRequiredPersonColumns(array $person)
    {
        return $this->hasRequiredColumns($person, array('name', 'email'));
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
}
