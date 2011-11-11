<?php

namespace Application\DevBundle\DataTest\Generator;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class Basic extends AbstractGenerator
{
	public function run(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$this->_runGenPreMisc($db, $output);
		$output->writeln('');

		$this->_runGenPeople($db, $output);
		$output->writeln('');

		$this->_runGenTickets($db, $output);
	}



	/**
	 * Generates any data required before others, such as companies
	 */
	protected function _runGenPreMisc(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$output->write("<comment>\nGENERATING PRE-MISC\n</comment>\n");

		$num = $this->dataset->getNumCompanies();
		$output->write("Generating $num companies ... ");

		$c = 0;
		while ($c++ < $num) {
			$db->insert('person_company', array(
				'title' => Strings::randomPronounceable(10)
			));
		}
	}



	/**
	 * Generates users and related user data
	 */
	protected function _runGenPeople(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$output->write("<comment>\nGENERATING PEOPLE\n</comment>\n");

		$num = $this->dataset->getNumPeople();
		$output->write("<info>Number of people being generated: $num</info>\n");

		$c = 0;
		$last_report_time = time();
		while ($c++ < $num) {
			if (time() - 10 > $last_report_time) {
				$last_report_time = time();
				$output->write("[$last_report_time] Processed $c\n");
			}

			$db->beginTransaction();

			#------------------------------
			# User
			#------------------------------

			$fname = Strings::randomPronounceable(mt_rand(4, 10));
			$lname = Strings::randomPronounceable(mt_rand(4, 20));

			$userinfo = array(
				'language_id' => $this->chooseFromChanceArray($this->dataset->getPersonLanguage(), 'languages'),
				'is_user' => 1,
				'full_name' => "$fname $lname",
				'informal_name' => mt_rand(0,1) ? '' : Strings::randomPronounceable(mt_rand(2, 8)),
				'nick_name' => mt_rand(0,1) ? '' : Strings::randomPronounceable(mt_rand(2, 8)),
				'secret_string' => '',
				'created_at' => $this->chooseDateFromChanceArray($this->dataset->getStartDate(), 'start_date')->format('Y-m-d H:i:s')
			);

			$db->insert('people', $userinfo);
			$person_id = $db->lastInsertId();


			#------------------------------
			# Email addresses
			#------------------------------

			$num_emails = $this->chooseFromChanceArray($this->dataset->getNumEmailsPerPerson(), 'num_emails');
			$primary_email_id = null;
			while ($num_emails-- > 0) {
				$email_domain = $this->chooseFromChanceArray($this->dataset->getEmailDomains(), 'email_domains');
				if (!$email_domain) $email_domain = Strings::random(mt_rand(3, 10), Strings::CHARS_ALPHA_I) . '.com';

				$email = Strings::random(mt_rand(3, 15), Strings::CHARS_ALPHA_I) . '@' . $email_domain;

				$created_at = $this->chooseDateFromChanceArray($this->dataset->getStartDate(), 'start_date')->format('Y-m-d H:i:s');

				$db->insert('person_emails', array(
					'person_id' => $person_id,
					'email' => $email,
					'is_validated' => 1,
					'created_at' => $created_at,
					'validated_at' => $created_at
				));

				if (!$primary_email_id) $primary_email_id = $db->lastInsertId();
			}

			if ($primary_email_id) {
				$db->update('people', array('primary_email_id' => $primary_email_id), array('id' => $person_id));
			}


			#------------------------------
			# Companies
			#------------------------------

			$add_company = $this->chooseFromChanceArray($this->dataset->getCompanyPerPerson(), 'company_choice');
			if (!$add_company) {
				$company_id = mt_rand($add_company[0], $add_company[1]);
				$db->insert('person2company', array(
					'person_id' => $person_id,
					'company_id' => $company_id
				));
			}

			$db->commit();
		}

		$num_techs = $this->dataset->getNumTechs();
		$output->write("<info>Setting $num_techs people as techs</info>\n");
		$db->executeUpdate("
			UPDATE people
			SET is_tech = 1
			WHERE id BETWEEN 1 AND $num_techs
		");
	}



	/**
	 * Generates tickets
	 */
	protected function _runGenTickets(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$output->write("<comment>\nGENERATING TICKETS\n</comment>\n");

		$count = 0;

		$last_report_time = time();
		for ($i = $this->dataset->getNumTechs()+1; $i < $this->dataset->getNumPeople(); $i++) {
			if (time() - 10 > $last_report_time) {
				$last_report_time = time();
				$output->write("[$last_report_time] Processed $count tickets for $i users\n");
			}

			$num_tickets = $this->chooseFromChanceArray($this->dataset->getNumTicketsPerPerson(), 'num_tickets');
			$count += $num_tickets;
			$this->_genTicketsForUser($db, $i, $num_tickets);
		}

		$output->write("<info>Done adding tickets for every user. Now creating more tickets to meet minimum count of {$this->dataset->getMinNumTickets()}</info>\n");
		$range = array($this->dataset->getNumTechs()+1, $this->dataset->getNumPeople());
		while ($count < $this->dataset->getMinNumTickets()) {
			if (time() - 10 > $last_report_time) {
				$last_report_time = time();
				$output->write("[$last_report_time] Processed $count tickets\n");
			}

			$num_tickets = $this->chooseFromChanceArray($this->dataset->getNumTicketsPerPerson(), 'num_tickets');
			$count += $num_tickets;
			$this->_genTicketsForUser($db, mt_rand($range[0], $range[1]), $num_tickets);
		}

		$output->write("<comment>\nCREATING TECH PARTICIPANT RELATIONS\n</comment>\n");

		$max_ticket = $this->dataset->getMinNumTickets() - $this->dataset->getNumTechs();
		for ($i = 1; $i <= $this->dataset->getNumTechs(); $i++) {
			for ($x = 1; $x < $max_ticket; $x += (200 + $i)) {
				$basic_db->insert('ticket_participants', array(
					'ticket_id' => $x,
					'person_id' => $i
				));
			}
		}

		$output->write("Done.\n");
	}

	protected function _genTicketsForUser(\DeskPRO\DBAL\Connection $db, $person_id, $num_tickets)
	{
		$person = $db->fetchArray("SELECT * FROM people WHERE id = ?", array($person_id));

		while ($num_tickets-- > 0) {

			$db->beginTransaction();

			$created_at = $this->chooseDateFromChanceArray($this->dataset->getStartDate(), 'ticket_start_date');
			//format('Y-m-d H:i:s')

			$subject = array();
			$subject_word = $this->chooseFromChanceArray($this->dataset->getCommonSubjectWord(), 'ticket_subject');
			if ($subject_word) {
				$subject[] = $subject_word;
			}

			$x_size = mt_rand(1, 10);
			for ($x = 0; $x < $x_size; $x++) {
				$subject[] = Strings::randomPronounceable(mt_rand(3, 10));
			}

			shuffle($subject);

			$subject = implode(' ', $subject);

			$ticket = array(
				'person_id' => $person_id,
				'department_id' => $this->chooseFromChanceArray($this->dataset->getDepartmentIdChoices(), 'department_id'),
				'category_id' => $this->chooseFromChanceArray($this->dataset->getCategoryIdChoices(), 'category_id'),
				'tech_id' => mt_rand(1, $this->dataset->getNumTechs()),
				'language_id' => $this->chooseFromChanceArray($this->dataset->getPersonLanguage(), 'languages'),
				'opened_at' => $created_at->format('Y-m-d H:i:s'),
				'company_id' => null,
				'subject' => $subject
			);

			$company_range = $this->chooseFromChanceArray($this->dataset->getCompanyPerPerson(), 'company_choice');
			if ($company_range) {
				$ticket['company_id'] = mt_rand($company_range[0], $company_range[1]);
			}

			$status = $this->chooseFromChanceArray($this->dataset->getStatusChoices(), 'ticket_status');
			$ticket['status'] = $status[0];
			$ticket['sub_status'] = $status[1];

			if ($ticket['status'] == 'closed') {
				$ticket['closed_at'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
			} elseif ($ticket['status'] == 'awaiting_agent' AND $ticket['sub_status'] == 'awaiting_tech') {
				$ticket['awaiting_tech_at'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
			} elseif ($ticket['status'] == 'awaiting_agent' AND $ticket['sub_status'] == 'awaiting_user') {
				$ticket['pending_at'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
			}

			$db->insert('tickets', $ticket);
			$ticket_id = $db->lastInsertId();

			// Add participants
			$num_parts = $this->chooseFromChanceArray($this->dataset->getParticipantsPerTicket(), 'num_participants');
			while ($num_parts-- > 0) {
				$range = $this->chooseFromChanceArray($this->dataset->getParticipantPersonChance(), 'participant_id_range');
				$got_ids = array();
				do {
					$add_part_id = mt_rand($range[0], $range[1]);
				} while (in_array($add_part_id, $got_ids));
				$got_ids[] = $add_part_id;

				break;
				$db->insert('ticket_participants', array(
					'ticket_id' => $ticket_id,
					'person_id' => $add_part_id
				));
			}

			// Ticket fields
			$fields = $this->dataset->getTicketFields();

			foreach ($fields as $fieldinfo) {
				$this->_genTicketFieldData($db, $fieldinfo, $ticket_id);
			}

			$db->commit();
		}
	}

	protected function _genTicketFieldData(\DeskPRO\DBAL\Connection $db, $fieldinfo, $ticket_id)
	{
		switch ($fieldinfo[1]['type']) {
			case 'int':
				$data = array(
					'field_id' => $fieldinfo[0],
					'ticket_id' => $ticket_id,
					'value' => mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1]),
				);

				$db->insert('ticket_field_data', $data);
				break;

			case 'text':
				$data = array(
					'field_id' => $fieldinfo[0],
					'ticket_id' => $ticket_id,
					'value' => Strings::randomPronounceable(mt_rand(4, 40)),
				);

				$db->insert('ticket_field_data', $data);
				break;

			case 'choice':

				$parent_data = array(
					'field_id' => $fieldinfo[0],
					'ticket_id' => $ticket_id,
				);

				$db->insert('ticket_field_data', $parent_data);
				$parent_id = $db->lastInsertId();

				for ($i = 0; $i < $fieldinfo[1]['max_choices']; $i++) {
					$child_data = array(
						'field_id' => $fieldinfo[0],
						'ticket_id' => $ticket_id,
						'value' => mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1]),
						'parent_id' => $parent_id
					);
					$vals[] = $child_data['value'];

					$db->insert('ticket_field_data', $child_data);
				}

				$vals = ':' . implode(':', $vals) . ':';
				$db->update('ticket_field_data', array('value' => $vals), array('id' => $parent_id));

				break;
		}
	}
}
