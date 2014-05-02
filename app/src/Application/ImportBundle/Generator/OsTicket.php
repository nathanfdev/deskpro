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

/**
 * Description of OsTicket
 *
 * @author Abhinav Kumar <abhinav.kumar@deskpro.com>
 */
class OsTicket
{
	protected $db;
	
	public function __construct($config)
	{
		$db_host = $config['db_host'];
		$db_name = $config['db_name'];
		$db_username = $config['db_username'];
		$db_password = $config['db_password'];

		try {
			$this->db = new \PDO("mysql:dbname={$db_name};host={$db_host}", $db_username, $db_password);
		} catch (\PDOException $e) {
			echo $e->getMessage();
			exit;
		}
	}
	
	public function findAllTickets()
	{
		$query = 'SELECT * FROM ost_ticket t LEFT JOIN ost_ticket__cdata c ON t.ticket_id = c.ticket_id';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute();
		
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	public function findAllStaff()
	{
		$query = 'SELECT * FROM ost_staff';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute();
		
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	public function findAllUser()
	{
		$query = 'SELECT * FROM ost_user u LEFT JOIN ost_user_email e ON u.id = e.user_id';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute();
		
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function findDepartmentFromId($id)
	{
		$query = 'SELECT dept_name FROM ost_department WHERE dept_id = ?';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute(array($id));
		
		return $stmt->fetchColumn();
	}
	
	public function findUserEmailFromId($id)
	{
		$query = 'SELECT address FROM ost_user_email e'
		. ' LEFT JOIN ost_user u '
		. ' ON e.user_id=u.id'
		. ' WHERE u.id = ?';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute(array($id));
		
		return $stmt->fetchColumn();
	}
	
	public function findStaffEmailFromId($id)
	{
		$query = 'SELECT email FROM ost_staff WHERE id = ?';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute(array($id));
		
		return $stmt->fetchColumn();
	}
	
	public function findTeamNameFromId($id)
	{
		$query = 'SELECT name FROM ost_team WHERE id = ?';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute(array($id));
		
		return $stmt->fetchColumn();
	}
	
	public function findMessageThreadFromId($ticket_id)
	{
		$query = 'SELECT thread_type, staff_id, user_id, body, created FROM ost_ticket_thread WHERE ticket_id = ?';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute(array($ticket_id));
		
		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	public function findTimezoneFromId($id)
	{
		$query = 'SELECT timezone FROM ost_timezone WHERE id = ?';
		
		$stmt   = $this->db->prepare($query);
		
		$result = $stmt->execute(array($id));
		
		return $stmt->fetchColumn();
	}
	
	public function exportPeople()
	{
		$file_path = '/deskpro/www/app/src/Application/ImportBundle/Resources/docs/data_example/people/';

		$index = 1;

		foreach ($this->findAllStaff() as $person) {
			$transformedArray = array();
			
			$transformedArray['oid']		= $index;
			$transformedArray['is_agent']		= true;
			$transformedArray['first_name']		= $person['firstname'];
			$transformedArray['last_name']		= $person['lastname'];
			$transformedArray['timezone']		= $this->findTimezoneFromId($person['timezone_id']);
			$transformedArray['date_created']	= $person['created'];
			$transformedArray['emails']		= array($person['email']);

			$file_name = 'person' . $index . '.json';

			file_put_contents($file_path . $file_name, json_encode($transformedArray));

			echo $file_name, ' exported successfully!', PHP_EOL;
			
			$index++;
		}
		
		unset($person);
		
		foreach ($this->findAllUser() as $person) {
			$transformedArray = array();
			
			$transformedArray['oid']		= $index;
			$transformedArray['is_user']		= true;
			$transformedArray['name']		= $person['name'];
			$transformedArray['date_created']	= $person['created'];
			$transformedArray['emails']		= array($person['address']);

			$file_name = 'person' . $index . '.json';

			file_put_contents($file_path . $file_name, json_encode($transformedArray));

			echo $file_name, ' exported successfully!', PHP_EOL;
			
			$index++;
		}
	}
	
	public function exportTickets()
	{
		$ticketPath = '/deskpro/www/app/src/Application/ImportBundle/Resources/docs/data_example/tickets/';

		$index = 1;

		foreach ($this->findAllTickets() as $ticket) {
			//print_r($ticket);

			$transformedArray = array();

			$transformedArray['ref']		= $ticket['number'];
			$transformedArray['department']		= $this->findDepartmentFromId($ticket['dept_id']);
			$transformedArray['person']		= $this->findUserEmailFromId($ticket['user_id']);
			$transformedArray['agent']		= $this->findUserEmailFromId($ticket['staff_id']) ?: null;
			$transformedArray['agent_team']		= $this->findUserEmailFromId($ticket['team_id']) ?: null;
			$transformedArray['status']		= $ticket['closed'] ? 'resolved' : $ticket['isanswered'] ? 'awaiting_user' : 'awaiting_agent';
			$transformedArray['date_created']	= $ticket['created'];
			$transformedArray['subject']		= $ticket['subject'];
			$transformedArray['priority']		= $ticket['priority'];

			foreach ($this->findMessageThreadFromId($ticket['ticket_id']) as $message_thread) {
				if ($message_thread['thread_type'] === 'R' && $message_thread['staff_id']) {
					$person_email = $this->findStaffEmailFromId($message_thread['staff_id']);

				} elseif ($message_thread['thread_type'] === 'M' && $message_thread['user_id']) {
					$person_email = $this->findUserEmailFromId($message_thread['user_id']);
				}

				$transformedArray['messages'][] = array(
					'person'	=> $person_email,
					'date_created'	=> $message_thread['created'],
					'message_text'	=> $message_thread['body']
				);
			}

			$file_name = 'ticket' . $index++ . '.json';

			file_put_contents($ticketPath . $file_name, json_encode($transformedArray));

			echo $file_name, ' exported successfully!' . PHP_EOL;
		}
	}
	
	public function generateJson()
	{
		$this->exportPeople();
		$this->exportTickets();
	}
}