<?php

namespace Application\DevBundle\DataTest\Generator;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class Book extends AbstractGenerator
{
	public function run(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$this->_runGenPeople($db, $output);
		$output->writeln('');

		$this->_runGenTickets($db, $output);
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

			#------------------------------
			# User
			#------------------------------

			$fname = Strings::randomPronounceable(mt_rand(4, 10));
			$lname = Strings::randomPronounceable(mt_rand(4, 20));

			$userinfo = array(
				'full_name' => "$fname $lname",
			);

			$db->insert('users', $userinfo);
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

				$db->insert('user_emails', array(
					'user_id' => $person_id,
					'email' => $email,
				));
			}
		}
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
			$x = mt_rand(1, 300);
			while ($x < $max_ticket) {
				$db->executeQuery('REPLACE INTO book_subscriptions SET book_id = ?, user_id =?', array(
					$x,
					$i
				));

				$x += mt_rand(1,300);
			}
		}

		$output->write("Done.\n");
	}

	protected function _genTicketsForUser(\DeskPRO\DBAL\Connection $db, $person_id, $num_tickets)
	{
		$person = $db->fetchArray("SELECT * FROM users WHERE id = ?", array($person_id));

		while ($num_tickets-- > 0) {

			$created_at = $this->chooseDateFromChanceArray($this->dataset->getStartDate(), 'ticket_start_date');

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
				'user_id' => $person_id,
				'organization_id' => $this->chooseFromChanceArray($this->dataset->getDepartmentIdChoices(), 'department_id'),
				'owner_id' => mt_rand(1, $this->dataset->getNumTechs()),
				'sizetype' => mt_rand(1,3),
				'created_at' => $created_at->format('Y-m-d H:i:s'),
			);

			$db->insert('books', $ticket);
			$ticket_id = $db->lastInsertId();

			$db->insert('book_titles', array(
				'book_id' => $ticket_id,
				'title' => $subject
			));

			// Add participants
			$num_parts = $this->chooseFromChanceArray($this->dataset->getParticipantsPerTicket(), 'num_participants');
			while ($num_parts-- > 0) {
				$range = $this->chooseFromChanceArray($this->dataset->getParticipantPersonChance(), 'participant_id_range');
				$got_ids = array();
				do {
					$add_part_id = mt_rand($range[0], $range[1]);
				} while (in_array($add_part_id, $got_ids));
				$got_ids[] = $add_part_id;

				$db->executeQuery('REPLACE INTO book_subscriptions SET book_id = ?, user_id =?', array(
					$ticket_id,
					$add_part_id
				));
			}

			// Ticket fields
			$fields = $this->dataset->getTicketFields();

			foreach ($fields as $fieldinfo) {
				$this->_genTicketFieldData($db, $fieldinfo, $ticket_id);
			}
		}
	}

	protected function _genTicketFieldData(\DeskPRO\DBAL\Connection $db, $fieldinfo, $ticket_id)
	{
		switch ($fieldinfo[1]['type']) {
			case 'int':
				$data = array(
					'info_id' => $fieldinfo[0],
					'book_id' => $ticket_id,
					'value' => mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1]),
				);

				$db->insert('book_info', $data);
				break;

			case 'text':

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

				$data = array(
					'info_id' => $fieldinfo[0],
					'book_id' => $ticket_id,
					'value_input' => $subject
				);

				$db->insert('book_info', $data);
				break;

			case 'choice':

				$values = array();
				for ($i = 0; $i < $fieldinfo[1]['max_choices']; $i++) {
					$values[] = $fieldinfo[0] + mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1]);
				}
				$values = array_unique($values);

				foreach ($values as $v) {
					$child_data = array(
						'info_id' => $v,
						'book_id' => $ticket_id,
						'value' => 1
					);

					$db->insert('book_info', $child_data);
				}

				break;
		}
	}
}
