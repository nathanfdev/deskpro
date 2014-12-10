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

namespace Application\ImportBundle\Generator;

use Application\ImportBundle\GeneratorConfig;

/**
 * Description of OsTicket
 *
 * @author Abhinav Kumar <abhinav.kumar@deskpro.com>
 */
class Csv implements GeneratorInterface
{
    /** @var string */
    protected $input_path;
    /** @var string */
    protected $output_path;
    /** @var int|null */
    protected $ticket_offset;
    /** @var int */
    protected $batch_size;

    /** @var array */
    protected $read_ticket_ids = array();

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var GeneratorConfig */
    protected $config;

    public function __construct(GeneratorConfig $config, $logger)
    {
        $this->config = $config;

        $this->input_path = $config->input_path;

        $this->output_path = $config->output_path;

        $this->batch_size = 10;

        $this->logger = $logger;
    }

    protected function getFileName($data_source)
    {
        return $this->input_path . DIRECTORY_SEPARATOR . $data_source . '.csv';
    }


    protected function getFile($data_source)
    {
        $data_file = $this->getFileName($data_source);

        return fopen($data_file, 'rt');
    }

    public function getRecordCount($data_source)
    {
        $data_file = $this->getFile($data_source);

        $records = -1;

        $line1 = fgets($data_file);
        rewind($data_file);

        if (substr_count($line1, ';') > substr_count($line1, ',')) {
            $sep = ';';
        } else {
            $sep = ',';
        }

        while (($row = fgetcsv($data_file, 4096, $sep)) !== false) {
            ++$records;
        }

        fclose($data_file);

        return $records;
    }

    public function exportPeople()
    {
        $index = 1;

        $output_file_path = $this->output_path . 'people/';

        foreach ($this->getData('people') as $person) {
            if (empty($person)) {
                $index++;
                $this->config->progress_bar->advance();
                continue;
            }

            if (!isset($person['email'])) {
                $this->logger->warning(sprintf('Invalid person record found (Skipping): %s'));
                $index++;
                $this->config->progress_bar->advance();
                continue;
            }

            if (empty($person['name'])) {
                $e = explode('@', $person['email'], 2);
                $person['name'] = $e[0];
            }

            $file_name = 'person' . $index . '.json';

            $names = explode(' ', $person['name']);

            $transformedArray = array();

            $transformedArray['oid']		= $index;
            $transformedArray['is_agent']		= isset($person['is_agent']) ? (bool) $person['is_agent'] : FALSE;
            $transformedArray['first_name']		= $names[0];
            $transformedArray['last_name']		= isset($names[1]) ? $names[1] : '';
            $transformedArray['emails']		= array($person['email']);

            if ($this->config->mode === 'live') {
                file_put_contents($output_file_path . $file_name, json_encode($transformedArray));
            }

            $this->logger->info(sprintf('%s exported successfully!', $file_name));

            $this->config->progress_bar->advance();

            $index++;
        }

    }

    public function exportTickets()
    {
        $index = 1;

        $output_file_path = $this->output_path . 'tickets/';

        foreach ($this->getData('tickets') as $ticket) {
            if (empty($ticket)) {
                $index++;
                $this->config->progress_bar->advance();
                continue;
            }

            if (!isset($ticket['subject']) || !isset($ticket['user'])) {
                $this->logger->warning(sprintf('Invalid ticket record found (Skipping)'));
                $index++;
                $this->config->progress_bar->advance();
                continue;
            }

            $file_name = 'ticket_' . trim($ticket['id']) . '.json';

            $transformedArray = array();

            $transformedArray['ref']		= $ticket['id'];
            $transformedArray['person']		= $ticket['user'];
            $transformedArray['agent']		= isset($ticket['agent']) ? $ticket['agent'] : null;
            $transformedArray['status']		= isset($ticket['status']) ? $ticket['status'] : 'awaiting_agent';
            $transformedArray['date_created']	= isset($ticket['date_created']) ? $ticket['date_created'] : date('Y-m-d H:i:s');
            $transformedArray['subject']		= $ticket['subject'];

            if ($this->config->mode === 'live') {
                file_put_contents($output_file_path . $file_name, json_encode($transformedArray));
            }

            $this->logger->info(sprintf('%s exported successfully!', $file_name));
            $this->read_ticket_ids[$ticket['id']] = true;

            $this->config->progress_bar->advance();

            $index++;
        }
    }

    public function exportTicketMessages()
    {
        $output_file_path = $this->output_path . 'tickets/';

        $index = 0;

        foreach ($this->getData('messages') as $ticket_message) {
            if (empty($ticket_message)) {
                $index++;
                $this->config->progress_bar->advance();
                continue;
            }

            if (!isset($ticket_message['message_text']) || !isset($ticket_message['user'])) {
                $this->logger->warning(sprintf('Invalid ticket message record found (Skipping)'));
                $index++;
                $this->config->progress_bar->advance();
                continue;
            }

            $ticket_id = trim($ticket_message['ticket_id']);

            $ticket_file_name = 'ticket_' . $ticket_id . '.json';

            $ticket_file_path = $output_file_path . $ticket_file_name;

            if ($this->config->mode === 'live') {
                if (is_writable($ticket_file_path)) {
                    $ticket_array = json_decode(file_get_contents($ticket_file_path), true);

                    $message = array(
                        'person' => $ticket_message['user'],
                        'date_created' => isset($ticket_message['date_created']) ? $ticket_message['date_created'] : date('Y-m-d H:i:s'),
                        'message_text' => $ticket_message['message_text']
                    );

                    @$ticket_array['messages'][] = $message;

                    if ($this->config->mode === 'live') {
                        file_put_contents($ticket_file_path, json_encode($ticket_array));
                    }

                    $this->logger->info(sprintf('%s exported successfully!', $ticket_file_path));

                } else {
                    $this->logger->warning(sprintf('Source ticket file for ticket_%s not found', $ticket_id));
                }
            } else {
                if (!isset($this->read_ticket_ids[$ticket_id])) {
                    $this->logger->warning(sprintf('Source record for ticket #%s not read', $ticket_id));
                }
            }

            $index++;

            $this->config->progress_bar->advance();
        }
    }

    public function getData($data_source)
    {
        $handle = $this->getFile($data_source);

        $header = NULL;
        $header_count = 0;

        $data = array();

        $line1 = fgets($handle);
        rewind($handle);

        if (substr_count($line1, ';') > substr_count($line1, ',')) {
            $sep = ';';
        } else {
            $sep = ',';
        }

        if ($handle) {
            while (($row = fgetcsv($handle, 0, $sep)) !== FALSE) {
                if(!$header) {
                    $header = array_map('trim',$row);
                    $header_count = count($header);
                } else {
                    $c = count($row);

                    // Fixes mis-matching column counts
                    if ($header_count > $c) {
                        // missing header cols, empty value
                        for ($i = $c; $i < $header_count; $i++) $row[$i] = '';
                    } else if ($c > $header_count) {
                        // too many cols, discard them
                        for ($i = $header_count; $i < $c; $i++) unset($row[$i]);
                    }

                    $data[] = array_combine($header, array_map('trim',$row));
                }
            }

            fclose($handle);
        }

        return $data;
    }


    public function generateJson()
    {
        $steps = $this->getRecordCount('people') +
            $this->getRecordCount('tickets') +
            $this->getRecordCount('messages');

        $this->config->progress_bar->start($this->config->output, $steps);

        try {
            $this->exportPeople();
            $this->exportTickets();
            $this->exportTicketMessages();
        } catch (\Exception $ex) {
            $this->logger->warning($ex->getMessage());
        }
    }
}
