<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
	protected $input_path;
	
	protected $output_path;
	
	protected $ticket_offset;
	
	protected $batch_size;
	
	/** @var Psr\Log\LoggerInterface */
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
		
		while (($row = fgetcsv($data_file, 4096, ';')) !== false) {
		    ++$records;
		}
		
		fclose($data_file);
		
		return $records;
	}
	
	public function exportPeople()
	{
		$output_file_path = $this->output_path . 'people/';
		
		$input_file = $this->getFile('people');
		
		$index = 0;
		
		while (($row = fgetcsv($input_file, 4096, ';')) !== false) {
			if ($index === 0) {
				$index++;
				continue;
			}
			
			$file_name = 'person' . $index . '.json';
			
			$names = explode(' ', $row[0]);
			
			$transformedArray = array();

			$transformedArray['oid']		= $index;
			$transformedArray['is_agent']		= $row[2];
			$transformedArray['first_name']		= $names[0];
			$transformedArray['last_name']		= isset($names[1]) ? $names[1] : '';
			$transformedArray['emails']		= array($row[1]);

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
		$output_file_path = $this->output_path . 'tickets/';
		
		$input_file = $this->getFile('tickets');
		
		$index = 0;
		
		while (($row = fgetcsv($input_file, 4096, ';')) !== false) {
			if ($index === 0) {
				$index++;
				continue;
			}
			
			$file_name = 'ticket_' . trim($row[0]) . '.json';
			
			$transformedArray = array();

			$transformedArray['ref']		= $row[0];
			$transformedArray['person']		= $row[3];
			$transformedArray['agent']		= $row[4];
			$transformedArray['status']		= $row[2];
			$transformedArray['date_created']	= $row[5];
			$transformedArray['subject']		= $row[1];

			if ($this->config->mode === 'live') {
				file_put_contents($output_file_path . $file_name, json_encode($transformedArray));
			}
			
			$this->logger->info(sprintf('%s exported successfully!', $file_name));
			
			$this->config->progress_bar->advance();

			$index++;
		}
		
	}
	
	public function exportTicketMessages()
	{
		$output_file_path = $this->output_path . 'tickets/';
		
		$input_file = $this->getFile('messages');
		
		$index = 0;
		
		while (($row = fgetcsv($input_file, 4096, ';')) !== false) {
			if ($index === 0) {
				$index++;
				continue;
			}
			
			$ticket_id = trim($row[0]);
			
			$ticket_file_name = 'ticket_' . $ticket_id . '.json';
			
			$ticket_file_path = $output_file_path . $ticket_file_name;
			
			if (is_writable($ticket_file_path)) {
				$ticket_array = json_decode(file_get_contents($ticket_file_path), true);
			
				$message = array(
					'person'	=> $row[1],
					'date_created'	=> $row[3],
					'message_text'	=> $row[2]
				);
				
				@$ticket_array['messages'][] = $message;

				if ($this->config->mode === 'live') {
					file_put_contents($ticket_file_path, json_encode($ticket_array));
				}

				$this->logger->info(sprintf('%s exported successfully!', $ticket_file_path));
				
			} else {
				$this->logger->warning(sprintf('Source ticket file for ticket_%s not found', $ticket_id));
			}
			
			$index++;
			
			$this->config->progress_bar->advance();
			
		}
		
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