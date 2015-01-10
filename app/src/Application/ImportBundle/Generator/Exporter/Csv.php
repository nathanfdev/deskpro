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
final class Csv extends AbstractExporter
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
     * Export people csv file
     *
     * @return Entity\Collection
     */
    private function exportPeople()
    {
        $collection = new Entity\Collection();
        $people = $this->getDataByRecordType(self::FILE_PEOPLE);
        foreach ($people as $num => $person) {
            $this->advanceProgressBar();

            if (!isset($person['email'])) {
                $this->logWarning(sprintf('Invalid person record found (Skipping): %d', $num));
                continue;
            }
            if (empty($person['name'])) {
                $e = explode('@', $person['email'], 2);
                $person['name'] = $e[0];
            }

            $names = explode(' ', $person['name']);
            $person_entity = new Entity\Person();
            $person_entity
                ->setDestination('person_' . $num)
                ->setOid($num)
                ->setAsAgent(isset($person['is_agent']) ? (bool)$person['is_agent'] : false)
                ->setFirstName($names[0])
                ->setLastName(isset($names[1]) ? $names[1] : '')
                ->setDateCreated(new DateTime())
                ->addEmail($person['email']);

            $collection->attach($person_entity);
            $this->logInfo(sprintf('%s exported successfully!', $person_entity->getDestination()));
        }

        return $collection;
    }

    /**
     * Export tickets csv file
     *
     * @return Entity\Collection
     */
    private function exportTickets()
    {
        $ticket_collection  = new Entity\Collection();
        $message_collection = new Entity\Collection();

        $tickets  = $this->getDataByRecordType(self::FILE_TICKETS);
        $messages = $this->getDataByRecordType(self::FILE_TICKET_MESSAGES);

        foreach ($messages as $num => $message) {
            if (!isset($message['message_text']) || !isset($message['user'])) {
                $this->logWarning(sprintf('Invalid ticket message record found (Skipping): %d', $num));
                continue;
            }

            $message_entity = new Entity\TicketMessage();
            $message_entity
                ->setDestination('ticket_' . trim($message['ticket_id']))
                ->setPersonEmail($message['user'])
                ->setMessageText($message['message_text'])
                ->setDateCreated(isset($message['date_created']) ? new DateTime($message['date_created']) : new DateTime());

            $message_collection->attach($message_entity);
        }

        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();

            if (!isset($ticket['subject']) || !isset($ticket['user'])) {
                $this->logWarning(sprintf('Invalid ticket record found (Skipping): %d', $num));
                continue;
            }

            $ticket_entity = new Entity\Ticket();
            $ticket_entity
                ->setDestination('ticket_' . trim($ticket['id']))
                ->setRef($ticket['id'])
                ->setPerson($ticket['user'])
                ->setAgent(isset($ticket['agent']) ? $ticket['agent'] : null)
                ->setStatus(isset($ticket['status']) ? $ticket['status'] : Entity\Ticket::STATUS_AWAITING_AGENT)
                ->setDateCreated(isset($ticket['date_created']) ? new DateTime($ticket['date_created']) : new DateTime())
                ->setSubject($ticket['subject']);

            foreach ($message_collection as $message_entity) {
                /** @var Entity\TicketMessage $message_entity */
                if ($ticket_entity->getDestination() === $message_entity->getDestination()) {
                    $ticket_entity->addMessage($message_entity);
                }
            }

            $ticket_collection->attach($ticket_entity);
            $this->logInfo(sprintf('%s exported successfully!', $ticket_entity->getDestination()));
        }

        return $ticket_collection;
    }
}
