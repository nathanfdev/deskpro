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
 * @package Importer
 */

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Value\TicketValue;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Validator\StringEmail;

class TicketValueImporter extends AbstractValueImporter
{
	/**
	 * @param mixed $tval
	 * @throws \Application\ImportBundle\Exception\BadDataException
	 */
	public function importValue($tval)
	{
		//var_dump($tval->organization); die;
		if (!($tval instanceof TicketValue)) {
			throw new \InvalidArgumentException("This importer can only import TicketValue");
		}
		
		$log_id = "Ticket :: " . $tval->oid . " ";
		
		$ticketId = null;
		
		$record = array();
		
		#------------------------------
		# Grab the person
		#------------------------------
		if ($tval->person && StringEmail::isValueValid($tval->person)) {
			$personId = $this->getMappers()->findIdFromMappedValue('person', $tval->person);
			
			if ($personId) {
				$this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $tval->person));
				$record['person_id'] = $personId;
			} else {
				$this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $tval->person));
				return false;
			}
		} else {
			throw new BadDataException("TicketValue must have a person specified");
		}
		
		if (!$tval->subject) {
			throw new BadDataException("TicketValue must have a subject specified");
		}
		
		$record['subject'] = $tval->subject;
		
		#------------------------------
		# Agent
		#------------------------------
		if ($tval->agent && StringEmail::isValueValid($tval->agent)) {
			$personId = $this->getMappers()->findIdFromMappedValue('person', $tval->agent);
			
			if ($personId) {
				$this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $tval->person));
				$record['agent_id'] = $personId;
			}
		}
		
		#------------------------------
		# Departments
		#------------------------------
		if ($tval->department) {
			$departmentId = $this->getMappers()->findIdFromMappedValue('department', $tval->department);
			
			if ($departmentId) {
				$this->getLogger()->info(sprintf("[%s] Found existing department %s", $log_id, $tval->department));
				$record['department_id'] = $departmentId;
			} else {
				$this->getLogger()->warning(sprintf("[%s] New Department found %s (creating)", $log_id, $tval->department));
				
				$this->getDb()->insert('departments', array(
						'title'			=> $tval->department,
						'is_tickets_enabled'	=> 1
					));
				
				$record['department_id'] = $this->getDb()->lastInsertId();
			}
		}
		
		// Lang
		if ($tval->language) {
			$languageId = $this->getMappers()->findIdFromMappedValue('language', $tval->language);
			if ($languageId) {
				$this->getLogger()->notice(sprintf("[%s] Found existing language %s", $log_id, $tval->language));
				$record['language_id'] = $languageId;
			} else {
				$this->getLogger()->notice(sprintf("[%s] Could not map language value: %s (skipping)", $log_id, $tval->language));
			}
		}
		
		// Categories
		if ($tval->category) {
			$categoryId = $this->getMappers()->findIdFromMappedValue('ticket_category', $tval->category);
			if ($categoryId) {
				$this->getLogger()->notice(sprintf("[%s] Found existing ticket category %s", $log_id, $tval->category));
				$record['category_id'] = $categoryId;
			} else {
				$this->getLogger()->notice(sprintf("[%s] Could not map ticket category value: %s (creating)", $log_id, $tval->category));
				
				$this->getDb()->insert('ticket_categories', array(
						'title'			=> $tval->category
					));
				
				$record['category_id'] = $this->getDb()->lastInsertId();
			}
		}
		
		// Priority
		if ($tval->priority) {
			$priorityId = $this->getMappers()->findIdFromMappedValue('ticket_priority', $tval->priority);
			if ($categoryId) {
				$this->getLogger()->notice(sprintf("[%s] Found existing ticket priority %s", $log_id, $tval->priority));
				$record['priority_id'] = $priorityId;
			} else {
				$this->getLogger()->notice(sprintf("[%s] Could not map ticket priority value: %s (creating)", $log_id, $tval->priority));
				
				$this->getDb()->insert('ticket_priorities', array(
						'title'			=> $tval->priority
					));
				
				$record['priority_id'] = $this->getDb()->lastInsertId();
			}
		}
		
		//Status
		if ($tval->status && 
			$this->getMappers()->getMapper ('ticket_status')->isValidStatus($tval->status)) {
			$this->getLogger()->notice(sprintf("[%s] Found existing ticket status %s", $log_id, $tval->status));
			$record['status'] = $tval->status;
		}
		
		//Date fields
		$record['date_created']		= $tval->date_created ? $tval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
		$record['date_closed']		= $tval->date_closed ? $tval->date_closed->format('Y-m-d H:i:s') : null;
		$record['date_resolved']	= $tval->date_resolved ? $tval->date_resolved->format('Y-m-d H:i:s') : null;
		
		// Org
		if ($tval->organization) {
			$organizationId = $this->getMappers()->findIdFromMappedValue('organization', $tval->organization);
			if ($organizationId) {
				$this->getLogger()->notice(sprintf("[%s] Found existing organization \"%s\"", $log_id, $tval->organization));
				$record['organization_id'] = $organizationId;
			} else {
				$this->getLogger()->notice(sprintf("[%s] New organization %s", $log_id, $tval->organization));
				if ($this->isTestMode()) {
					$record['organization_id'] = -1;
				} else {
					$this->getDb()->insert('organizations', array(
						'name'         => $tval->organization,
						'date_created' => date('Y-m-d H:i:s')
					));
					$record['organization_id'] = $this->getDb()->lastInsertId();
				}

				$this->getMappers()->learnMapping('organization', $record['organization_id']);
			}
		}
		
		#------------------------------
		# Save data
		#------------------------------
		
		if (!$this->isTestMode()) {
			$this->getDb()->insert('tickets', $record);
			
			$ticketId = $this->getDb()->lastInsertId();
			
			$is_new = true;

			$this->getLogger()->info(sprintf("[%s] Created %d", $log_id, $ticketId));
		}
		
		
		#------------------------------
		# Participants
		#------------------------------
		if ($ticketId && $tval->participants) {
			$tval->participants = array_filter($tval->participants, function($email) {
				return StringEmail::isValueValid($email);
			});
			
			if ($tval->participants) {
				$batch = array();
				foreach ($tval->participants as $participant) {
					$participantId = $this->getMappers()->findIdFromMappedValue('person', $participant);
					
					if ($participantId) {
						$batch = array('ticket_id' => $ticketId, 'person_id' => $participantId);
						$this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $participant));
						$this->getDb()->insert('tickets_participants', $batch);
					} else {
						$this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $participant));
					}
				}
			}
		}
		
		#------------------------------
		# Ticket Messages
		#------------------------------
		if ($ticketId && $tval->messages) {
			foreach ($tval->messages as $message) {
				$record = array();
				
				$messagePersonId = $this->getMappers()->findIdFromMappedValue('person', $message->person);
				
				if ($messagePersonId) {
					$this->getLogger()->info(sprintf("[%s] Found existing person %s", $log_id, $tval->person));
					$record['person_id'] = $messagePersonId;
				} else {
					$this->getLogger()->warning(sprintf("[%s] Unknown person with email %s (skipping)", $log_id, $tval->person));
					continue;
				}
				
				$record['ticket_id'] = $ticketId;
				$record['message'] = $message->message_text;
				
				$this->getDb()->insert('tickets_messages', $record);
			}
		}
		
		#------------------------------
		# Labels
		#------------------------------
		if ($ticketId && $tval->labels) {
			$batch = array_map(function($l) use ($ticketId) {
				return array(
					'ticket_id' => $ticketId,
					'label'     => $l
				);
			}, $tval->labels);

			$this->getDb()->batchInsert('labels_tickets', $batch, true);
		}
	}
}

