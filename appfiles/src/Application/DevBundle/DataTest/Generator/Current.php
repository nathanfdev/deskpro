<?php

namespace Application\DevBundle\DataTest\Generator;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;

class Current extends AbstractGenerator
{
	public function run(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$this->_runGenPreMisc($db, $output);
		$output->writeln('');

		$db->exec("
			LOCK TABLES
			people WRITE,
			people_emails WRITE,
			tickets WRITE,
			tickets_participants WRITE,
			tickets_messages WRITE
		");

		$this->_runGenPeople($db, $output);
		$output->writeln('');

		$this->_runGenTickets($db, $output);

		$db->exec("UNLOCK TABLES");
	}



	/**
	 * Generates any data required before others, such as companies
	 */
	protected function _runGenPreMisc(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$output->write("<comment>\nGENERATING PRE-MISC\n</comment>\n");

		$num = $this->dataset->getNumCompanies();
		$output->write("Generating $num organizations ... ");

		$c = 0;
		while ($c++ < $num) {
			$db->insert('organizations', array(
				'name' => Strings::randomPronounceable(mt_rand(6,15))
			));
		}

		$num = $this->dataset->getNumProducts();
		$output->write("Generating $num products ... ");

		$c = 0;
		while ($c++ < $num) {
			$db->insert('products', array(
				'title' => "Product $c"
			));
		}

		$num = count($this->dataset->getDepartmentIdChoices());
		$output->write("Generating $num departments ... ");

		$this->total_cats = 0;
		$c = 0;
		while ($c++ < $num) {
			$db->insert('departments', array(
				'title' => "Department $c"
			));

			$dep_id = $db->lastInsertId();
			$this->dataset->dep_cat_ids[$dep_id] = array();

			$num_c = mt_rand(0, $this->dataset->getNumCategoriesPerDep());
			$c_c = 0;
			while ($c_c++ < $num_c) {
				$db->insert('ticket_categories', array(
					'department_id' => $dep_id,
					'title' => "Category $c_c"
				));

				$this->total_cats++;
				$this->dataset->dep_cat_ids[$dep_id][] = $c_c;
			}
		}

		$num = $this->dataset->getNumLangs();
		$output->write("Generating $num langs ... ");
		$c = 0;
		while ($c++ < $num) {
			$db->insert('languages', array(
				'title' => "Language $c",
				'locale' => 'en_US'
			));
		}

		$num = $this->dataset->getNumPriorities();;
		$output->write("Generating $num pris ... ");
		$c = 0;
		while ($c++ < $num) {
			$db->insert('ticket_priorities', array(
				'title' => "Priority $c",
				'priority' => $c
			));
		}

		$num = $this->dataset->getAdditionalUsergroups();
		$output->write("Generating $num additional usergroups ... ");
		$c = 0;
		while ($c++ < $num) {
			$db->insert('usergroups', array(
				'title' => "Usergroup $c",
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
		$output->write("\n<info>Number of people being generated: $num</info>\n");

		$c = 0;
		$last_report_time = time();
		while ($c++ < $num) {
			if (time() - 10 > $last_report_time) {
				$last_report_time = time();
				$output->write("\n[$last_report_time] Processed $c\n");
			}

			$output->write(".");

			//$db->beginTransaction();

			#------------------------------
			# User
			#------------------------------

			$fname = Strings::randomPronounceable(mt_rand(4, 10));
			$lname = Strings::randomPronounceable(mt_rand(4, 20));

			$userinfo = array(
				'language_id' => mt_rand(1, $this->dataset->getNumLangs()),
				'organization_id' => mt_rand(1, $this->dataset->getNumCompanies()),
				'organization_position' => Strings::randomPronounceable(mt_rand(5, 20)),
				'is_user' => 1,
				'is_contact' => 1,
				'name' => "$fname $lname",
				'first_name' => "$fname",
				'last_name' => "$lname",
				'secret_string' => 'secret',
				//'timezone' => 'UTC',
				'password' => sha1('password'.'salt'),
				'salt' => 'salt',
				'date_created' => $this->chooseDateFromChanceArray($this->dataset->getStartDate(), 'start_date')->format('Y-m-d H:i:s')
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

				$db->insert('people_emails', array(
					'person_id' => $person_id,
					'email' => $email,
					'is_validated' => 1,
					'date_created' => $created_at,
					'date_created' => $created_at
				));

				if (!$primary_email_id) $primary_email_id = $db->lastInsertId();
			}

			if ($primary_email_id) {
				$db->update('people', array('primary_email_id' => $primary_email_id), array('id' => $person_id));
			}

			//$db->commit();
		}

		$num_techs = $this->dataset->getNumTechs();
		$output->write("<info>Setting $num_techs people as techs</info>\n");
		$db->executeUpdate("
			UPDATE people
			SET is_agent = 1
			WHERE id BETWEEN 1 AND $num_techs
		");
	}



	/**
	 * Generates tickets
	 */
	protected function _runGenTickets(\DeskPRO\DBAL\Connection $db, \Symfony\Component\Console\Output\Output $output)
	{
		$output->write("\n<comment>\nGENERATING TICKETS\n</comment>\n");

		$count = 0;

		$last_report_time = time();
		for ($i = $this->dataset->getNumTechs()+1; $i < $this->dataset->getNumPeople(); $i++) {
			if (time() - 10 > $last_report_time) {
				$last_report_time = time();
				$output->write("\n[$last_report_time] Processed $count tickets for $i users\n");
			}

			$num_tickets = $this->chooseFromChanceArray($this->dataset->getNumTicketsPerPerson(), 'num_tickets');
			$count += $num_tickets;
			$this->_genTicketsForUser($db, $i, $num_tickets, $output);
		}

		$output->write("<info>Done adding tickets for every user. Now creating more tickets to meet minimum count of {$this->dataset->getMinNumTickets()}</info>\n");
		$range = array($this->dataset->getNumTechs()+1, $this->dataset->getNumPeople());
		while ($count < $this->dataset->getMinNumTickets()) {
			if (time() - 10 > $last_report_time) {
				$last_report_time = time();
				$output->write("[$last_report_time] Processed $count tickets\n");
			}

			$num_tickets = 100;
			$count += $num_tickets;
			$this->_genTicketsForUser($db, mt_rand($range[0], $range[1]), $num_tickets, $output);
		}

		$output->write("Done.\n");
	}

	protected function _genTicketsForUser(\DeskPRO\DBAL\Connection $db, $person_id, $num_tickets, $output)
	{
		$person = $db->fetchAssoc("SELECT * FROM people WHERE id = ?", array($person_id));

		while ($num_tickets-- > 0) {

			$output->write(".");

			//$db->beginTransaction();

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

			$dep_id = $this->chooseFromChanceArray($this->dataset->getDepartmentIdChoices(), 'department_id');
			$ticket = array(
				'person_id' => $person_id,
				'department_id' => $dep_id,
				'category_id' => mt_rand(1,$this->total_cats),
				'agent_id' => mt_rand(1, $this->dataset->getNumTechs()),
				'language_id' => mt_rand(1, $this->dataset->getNumLangs()),
				'priority_id' => mt_rand(1, $this->dataset->getNumPriorities()),
				'product_id' => mt_rand(1, $this->dataset->getNumProducts()),
				'date_created' => $created_at->format('Y-m-d H:i:s'),
				'organization_id' => $person['organization_id'],
				'creation_system' => 'web',
				'subject' => $subject
			);
			$ticket['date_first_agent_reply'] = $created_at->add(new \DateInterval('PT'.mt_rand(360, 86400).'S'))->format('Y-m-d H:i:s');
			$ticket['date_last_agent_reply'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
			$ticket['date_agent_waiting'] = $ticket['date_last_agent_reply'];
			$ticket['date_last_user_reply'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
			$ticket['date_user_waiting'] = $ticket['date_last_user_reply'];
			$ticket['total_user_waiting'] = mt_rand(600, 86400*4);
			$ticket['total_to_first_reply'] = mt_rand(120, $ticket['total_user_waiting']);

			$ticket['status'] = $this->chooseFromChanceArray($this->dataset->getStatusChoices(), 'ticket_status');

			if ($ticket['status'] == 'closed') {
				$ticket['date_closed'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
				$ticket['date_resolved'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
			} elseif ($ticket['status'] == 'awaiting_tech') {

			} elseif ($ticket['status'] == 'awaiting_user') {

			} elseif ($ticket['status'] == 'resolved') {
				$ticket['date_resolved'] = $created_at->add(new \DateInterval('PT'.mt_rand(4000, 345600).'S'))->format('Y-m-d H:i:s');
			} elseif ($ticket['status'] == 'hidden') {
				$ticket['hidden_status'] = 'spam';
			}

			$db->insert('tickets', $ticket);
			$ticket_id = $db->lastInsertId();

			// Add participants
			$num_parts = $this->chooseFromChanceArray($this->dataset->getParticipantsPerTicket(), 'num_participants');
			$got_ids = array();
			while ($num_parts-- > 0) {
				do {
					$add_part_id = mt_rand(20, $this->dataset->getNumPeople());
				} while (in_array($add_part_id, $got_ids));
				$got_ids[] = $add_part_id;

				break;
				$db->insert('tickets_participants', array(
					'ticket_id' => $ticket_id,
					'person_id' => $add_part_id
				));
			}

			// Messages
			$got_ids[] = $ticket['agent_id'];
			$got_ids[] = $ticket['person_id'];

			$num = mt_rand(2, 10);
			while ($num--) {
				$message_str = $this->dataset->getWords();
				shuffle($message_str);
				$message_str = array_slice($message_str, 0, count($message_str) - mt_rand(1, 20));
				$message_str = implode(' ', $message_str);
				$message = array(
					'ticket_id' => $ticket_id,
					'person_id' => $got_ids[array_rand($got_ids)],
					'date_created' => $created_at->add(new \DateInterval('PT'.mt_rand(500*$num, 600*$num).'S'))->format('Y-m-d H:i:s'),
					'message_hash' => sha1($message_str),
					'message' => $message_str
				);

				$db->insert('tickets_messages', $message);
			}

			// Ticket fields
			//$fields = $this->dataset->getTicketFields();

			//foreach ($fields as $fieldinfo) {
				//$this->_genTicketFieldData($db, $fieldinfo, $ticket_id);
			//}

			//$db->commit();
		}
	}

	protected function _genTicketFieldData(\DeskPRO\DBAL\Connection $db, $fieldinfo, $ticket_id)
	{
		switch ($fieldinfo[1]['type']) {
			case 'int':
				$data = array(
					'field_id' => $fieldinfo[0],
					'ticket_id' => $ticket_id,
					'value_int' => mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1]),
				);

				$db->insert('ticket_field_data', $data);
				break;

			case 'text':
				$data = array(
					'field_id' => $fieldinfo[0],
					'ticket_id' => $ticket_id,
					'value_str' => Strings::randomPronounceable(mt_rand(4, 40)),
				);

				$db->insert('ticket_field_data', $data);
				break;

			case 'choice':

				for ($i = 0; $i < $fieldinfo[1]['max_choices']; $i++) {
					$child_data = array(
						'field_id' => $fieldinfo[0],
						'ticket_id' => $ticket_id,
						'value_int' => mt_rand($fieldinfo[1]['range'][0], $fieldinfo[1]['range'][1])
					);

					$db->insert('ticket_field_data', $child_data);
				}

				break;
		}
	}
}