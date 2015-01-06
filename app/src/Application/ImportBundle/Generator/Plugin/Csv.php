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

namespace Application\ImportBundle\Generator\Plugin;

use Application\ImportBundle\CsvReader\CsvConfig;
use Application\ImportBundle\CsvReader\CsvReaderInterface;
use Orb\Util\Arrays;

/**
 * Description of Csv
 *
 * @author Abhinav Kumar <abhinav.kumar@deskpro.com>
 * @package Application\ImportBundle\Generator\Plugin
 */
class Csv extends AbstractPlugin
{
    /**
     * @var CsvReaderInterface
     */
    private $csv_reader;

    /**
     * @var array
     */
    private $read_ticket_ids = array();

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
        return self::GENERATOR_TYPE_CSV;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRecordsCount()
    {
        return $this->getRecordsCountByType(self::RECORD_TYPE_PEOPLE)
             + $this->getRecordsCountByType(self::RECORD_TYPE_TICKETS)
             + $this->getRecordsCountByType(self::RECORD_TYPE_MESSAGES);
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordsCountByType($type)
    {
        return $this->csv_reader->getRowsCount($this->getCsvReaderConfig($type));
    }

    /**
     * Returns exporting raw data
     *
     * @param string $type
     * @return array
     */
    protected function getDataByRecordType($type)
    {
        return $this->csv_reader->getData($this->getCsvReaderConfig($type));
    }

    /**
     * Get absolute file path
     *
     * @param string $record_type
     * @return CsvConfig
     */
    protected function getCsvReaderConfig($record_type)
    {
        return new CsvConfig(sprintf('%s/%s.csv', $this->config->getInputPath(), $record_type));
    }

    /**
     * {@inheritdoc}
     */
    public function generateJson()
    {
        try {
            $this->exportPeople();
            $this->exportTickets();
            $this->exportTicketMessages();

        } catch (\Exception $ex) {
            $this->logWarning($ex->getMessage());
        }
    }

    /**
     * @return void
     */
    protected function exportPeople()
    {
        $index = 1;
        $data  = $this->getDataByRecordType(self::RECORD_TYPE_PEOPLE);

        foreach ($data as $person) {
            if (!count(Arrays::removeEmptyString($person))) {
                $index++;
                $this->advanceProgressBar();
                continue;
            }
            if (!isset($person['email'])) {
                $this->logWarning(sprintf('Invalid person record found (Skipping): %s'));
                $index++;
                $this->advanceProgressBar();
                continue;
            }
            if (empty($person['name'])) {
                $e = explode('@', $person['email'], 2);
                $person['name'] = $e[0];
            }

            $file_name = 'person' . $index . '.json';
            $names = explode(' ', $person['name']);

            $transformed = array(
                'oid'        => $index,
                'is_agent'   => isset($person['is_agent']) ? (bool) $person['is_agent'] : false,
                'first_name' => $names[0],
                'last_name'  => isset($names[1]) ? $names[1] : '',
                'emails'     => array($person['email']),
            );

            if ($this->config->isLive()) {
                file_put_contents($this->getExportPeopleOutputPath() . $file_name, json_encode($transformed));
            }

            $this->logInfo(sprintf('%s exported successfully!', $file_name));
            $this->advanceProgressBar();

            $index++;
        }
    }

    /**
     * @return void
     */
    protected function exportTickets()
    {
        $index = 1;
        $data  = $this->getDataByRecordType(self::RECORD_TYPE_TICKETS);

        foreach ($data as $ticket) {
            if (!count(Arrays::removeEmptyString($ticket))) {
                $index++;
                $this->advanceProgressBar();
                continue;
            }
            if (!isset($ticket['subject']) || !isset($ticket['user'])) {
                $this->logWarning(sprintf('Invalid ticket record found (Skipping)'));
                $index++;
                $this->advanceProgressBar();
                continue;
            }

            $file_name = 'ticket_' . trim($ticket['id']) . '.json';
            $transformed = array(
                'ref'          => $ticket['id'],
                'person'       => $ticket['user'],
                'agent'        => isset($ticket['agent']) ? $ticket['agent'] : null,
                'status'       => isset($ticket['status']) ? $ticket['status'] : 'awaiting_agent',
                'date_created' => isset($ticket['date_created']) ? $ticket['date_created'] : date('Y-m-d H:i:s'),
                'subject'      => $ticket['subject'],
            );

            if ($this->config->isLive()) {
                file_put_contents($this->getExportTicketsOutputPath() . $file_name, json_encode($transformed));
            }

            $this->logInfo(sprintf('%s exported successfully!', $file_name));
            $this->read_ticket_ids[$ticket['id']] = true;

            $this->advanceProgressBar();

            $index++;
        }
    }

    /**
     * @return void
     */
    protected function exportTicketMessages()
    {
        $index = 0;
        $data  = $this->getDataByRecordType(self::RECORD_TYPE_MESSAGES);

        foreach ($data as $ticket_message) {
            if (!count(Arrays::removeEmptyString($ticket_message))) {
                $index++;
                $this->advanceProgressBar();
                continue;
            }

            if (!isset($ticket_message['message_text']) || !isset($ticket_message['user'])) {
                $this->logWarning(sprintf('Invalid ticket message record found (Skipping)'));
                $index++;
                $this->advanceProgressBar();
                continue;
            }

            $ticket_id = trim($ticket_message['ticket_id']);
            $ticket_file_name = 'ticket_' . $ticket_id . '.json';
            $ticket_file_path = $this->getExportTicketMessagesOutputPath() . $ticket_file_name;

            if ($this->config->isLive()) {
                if (is_file($ticket_file_path)) {
                    $ticket_array = json_decode(file_get_contents($ticket_file_path), true);

                    $message = array(
                        'person'       => $ticket_message['user'],
                        'date_created' => isset($ticket_message['date_created']) ? $ticket_message['date_created'] : date('Y-m-d H:i:s'),
                        'message_text' => $ticket_message['message_text']
                    );

                    @$ticket_array['messages'][] = $message;

                    if ($this->config->isLive()) {
                        file_put_contents($ticket_file_path, json_encode($ticket_array));
                    }

                    $this->logInfo(sprintf('%s exported successfully!', $ticket_file_path));

                } else {
                    $this->logWarning(sprintf('Source ticket file for ticket_%s not found', $ticket_id));
                }
            } else {
                if (!isset($this->read_ticket_ids[$ticket_id])) {
                    $this->logWarning(sprintf('Source record for ticket #%s not read', $ticket_id));
                }
            }

            $index++;
            $this->advanceProgressBar();
        }
    }
}
