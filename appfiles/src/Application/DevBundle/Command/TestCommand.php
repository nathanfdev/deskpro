<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \Application\DeskPRO\App;

use \Orb\Util\Strings;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected $lang_ids;
	protected $dep_ids;
	protected $cat_ids;
	protected $pri_ids;
	protected $work_ids;
	protected $prod_ids;
	protected $agent_ids;
	protected $agent_team_ids;
	protected $org_ids;
	protected $person_range = array(51, 65000);

	protected function _fill_cache()
	{
		$this->lang_ids = App::getDb()->fetchAllCol("SELECT id FROM languages WHERE parent_id IS NULL");
		$this->dep_ids = App::getDb()->fetchAllCol("SELECT id FROM departments WHERE parent_id IS NULL");
		$this->cat_ids = App::getDb()->fetchAllCol("SELECT id FROM ticket_categories WHERE parent_id IS NULL");
		$this->pri_ids = App::getDb()->fetchAllCol("SELECT id FROM ticket_priorities");
		$this->work_ids = App::getDb()->fetchAllCol("SELECT id FROM ticket_workflows");
		$this->prod_ids = App::getDb()->fetchAllCol("SELECT id FROM products");
		$this->agent_ids = App::getDb()->fetchAllCol("SELECT id FROM people WHERE is_agent = 1");
		$this->agent_team_ids = App::getDb()->fetchAllCol("SELECT id FROM agent_teams");
		$this->org_ids = App::getDb()->fetchAllCol("SELECT id FROM organizations");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		//$this->_fill_cache();
		//$this->_do_new_tickets(90000);
		//$this->_insert_ticketfield_data(281922, 350000);
		//$this->_insert_participant_data(96010, 350000);
		$this->_insert_ticket_map_data(283500, 350000);
	}
	protected function _insert_ticket_map_data($tid_start, $tid_end)
	{
		App::getDb()->beginTransaction();

		$tid = $tid_start;
		$x = 0;
		while($tid++ < $tid_end) {
			$x++;

			$ticket = App::getDb()->fetchAssoc('SELECT id, department_id, category_id, agent_id FROM tickets WHERE id = ?', array($tid));
			if (!$ticket) continue;

			if ($ticket['department_id']) {
				$rec = array('ticket_id' => $ticket['id'], 'department_id' => $ticket['department_id']);
				App::getDb()->insert('ticket_to_department', $rec);
			}

			if ($ticket['category_id']) {
				$rec = array('ticket_id' => $ticket['id'], 'category_id' => $ticket['category_id']);
				App::getDb()->insert('ticket_to_category', $rec);
			}

			if ($ticket['agent_id']) {
				$rec = array('ticket_id' => $ticket['id'], 'agent_id' => $ticket['agent_id']);
				App::getDb()->insert('ticket_to_agent', $rec);
			}

			if ($x % 2000 == 0) {
				App::getDb()->commit();
				App::getDb()->beginTransaction();
			}

			if ($x % 1000 == 0) {
				echo $tid .'. ';
			}
		}

		App::getDb()->commit();
	}

	protected function _insert_participant_data($tid_start, $tid_end)
	{
		App::getDb()->beginTransaction();

		$tid = $tid_start;
		$x = 0;
		while($tid++ < $tid_end) {
			$x++;

			$r = mt_rand(0, 10);
			if ($r < 4) {
				$r = 1;
			} else {
				$r = 2;
			}
			$pid = mt_rand(1, 63000);
			for ($times = 0; $times < $r; $times++) {
				$data = array(
					'ticket_id' => $tid,
					'person_id' => $pid
				);
				$pid += 10 + mt_rand(1,9);
				try {
					App::getDb()->insert('tickets_participants', $data);
				} catch (Exception $e) {}
			}

			if ($x % 2000 == 0) {
				App::getDb()->commit();
				App::getDb()->beginTransaction();
			}

			if ($x % 1000 == 0) {
				echo $tid .'. ';
			}
		}

		App::getDb()->commit();
	}

	protected function _insert_ticketfield_data($tid_start, $tid_end)
	{
		$states = array("Alabama","AL","Alaska","AK","Arizona","AZ","Arkansas","AR","California","CA","Colorado","CO","Connecticut","CT","Delaware","DE","District of Columbia","DC","Florida","FL","Georgia","GA","Hawaii","HI","Idaho","ID","Illinois","IL","Indiana","IN","Iowa","IA","Kansas","KS","Kentucky","KY","Louisiana","LA","Maine","ME","Montana","MT","Nebraska","NE","Nevada","NV","New Hampshire","NH","New Jersey","NJ","New Mexico","NM","New York","NY","North Carolina","NC","North Dakota","ND","Ohio","OH","Oklahoma","OK","Oregon","OR","Maryland","MD","Massachusetts","MA","Michigan","MI","Minnesota","MN","Mississippi","MS","Missouri","MO","Pennsylvania","PA","Rhode Island","RI","South Carolina","SC","South Dakota","SD","Tennessee","TN","Texas","TX","Utah","UT","Vermont","VT","Virginia","VA","Washington","WA","West Virginia","WV","Wisconsin","WI","Wyoming","WY");

		$f_text_1 = '1';
		$f_text_2 = '2';
		$f_choice_1 = '3';
		$f_choice_1_opts = range(4, 13);
		$f_choice_2 = '14';
		$f_choice_2_opts = range(15, 24);

		App::getDb()->beginTransaction();

		$tid = $tid_start;
		$x = 0;
		while($tid++ < $tid_end) {
				$x++;
				$data = array(
					'field_id' => $f_text_1,
					'ticket_id' => $tid,
					'value' => 0,
					'input' => mt_rand(1000,9999) . ' ' . $states[array_rand($states)]
				);
				App::getDb()->insert('custom_data_ticket', $data);

				$x++;
				$data = array(
					'field_id' => $f_text_2,
					'ticket_id' => $tid,
					'value' => 0,
					'input' => mt_rand(1000,9999) . ' ' . $states[array_rand($states)]
				);
				App::getDb()->insert('custom_data_ticket', $data);

				$x++;
				$choice = $f_choice_1_opts[array_rand($f_choice_1_opts)];
				$data = array(
					'field_id' => $f_choice_1,
					'ticket_id' => $tid,
					'value' => $choice,
					'input' => ''
				);
				App::getDb()->insert('custom_data_ticket', $data);

				$x++;
				$choice = $f_choice_2_opts[array_rand($f_choice_2_opts)];
				$data = array(
					'field_id' => $f_choice_2,
					'ticket_id' => $tid,
					'value' => $choice,
					'input' => ''
				);
				App::getDb()->insert('custom_data_ticket', $data);

			if ($x % 2000 == 0) {
				App::getDb()->commit();
				App::getDb()->beginTransaction();
			}

			if ($x % 1000 == 0) {
				echo $tid .'. ';
			}
		}

		App::getDb()->commit();
	}

	protected function _do_new_tickets($num = 90000)
	{
		echo "Starting tickets\n";
		App::getDb()->beginTransaction();

		while ($num--) {
			$ticket = array();

			$ticket['language_id'] = $this->chooseOptionFrom($this->lang_ids, 60);
			$ticket['department_id'] = $this->chooseOptionFrom($this->dep_ids);
			$ticket['category_id'] = $this->chooseOptionFrom($this->cat_ids, 20);
			$ticket['priority_id'] = $this->chooseOptionFrom($this->pri_ids, 30);
			$ticket['workflow_id'] = $this->chooseOptionFrom($this->work_ids, 30);
			$ticket['person_id'] = mt_rand($this->person_range[0], $this->person_range[1]);
			$ticket['agent_id'] = $this->chooseOptionFrom($this->agent_ids, 30);
			$ticket['agent_team_id'] = $this->chooseOptionFrom($this->agent_team_ids, 30);
			$ticket['organization_id'] = $this->chooseOptionFrom($this->org_ids, 35);
			$ticket['creation_system'] = 'web_person';

			$r = mt_rand(1, 100);
			if ($r < 40) {
				$ticket['is_archived'] = 1;
				$ticket['status'] = 'closed';
			} else {
				$ticket['is_archived'] = 0;
				if ($r < 70) {
					$ticket['status'] = 'resolved';
				} elseif ($r < 80) {
					$ticket['status'] = 'open';
				} else {
					$ticket['status'] = 'pending';
				}
			}

			$ticket['urgency'] = mt_rand(1, 100);
			$ticket['date_created'] = $this->randDate();

			if ($ticket['status'] == 'resolved' OR $ticket['status'] == 'closed') {
				$ticket['date_resolved'] = $this->randDate();
				if ($ticket['status'] == 'closed') {
					$ticket['date_closed'] = $this->randDate();
				}
			}

			$ticket['date_first_agent_reply'] = $this->randDate();
			$ticket['date_last_agent_reply'] = $this->randDate();
			$ticket['date_last_user_reply'] = $this->randDate();
			$ticket['date_agent_waiting'] = $this->randDate();
			$ticket['date_user_waiting'] = $this->randDate();
			$ticket['total_user_waiting'] = mt_rand(300, 2419200);
			$ticket['total_to_first_reply'] = mt_rand(300, 2419200);
			$ticket['has_attachments'] = (mt_rand(1,100) < 30) ? 1 : 0;
			$ticket['subject'] = $this->randString(10, 100);

			App::getDb()->insert('tickets', $ticket);
			$id = App::getDb()->lastInsertId();
			echo $id . '. ';

			if ($num % 1000 == 0) {
				App::getDb()->commit();
				App::getDb()->beginTransaction();
			}
		}

		App::getDb()->commit();

		echo "\nDone\n";
	}

	protected function randString($min, $max)
	{
		$str = '';
		$target = mt_rand($min, $max);

		while(strlen($str) < $target) {
			$str .= Strings::randomPronounceable(mt_rand(3, 10)) . ' ';
		}

		$str = trim($str);

		return $str;
	}

	protected function randDate($chance_null = 0)
	{
		if ($chance_null AND mt_rand(1, 100) < $chance_null) return null;
		$t = time() - mt_rand(1000, 63113851);

		return date('Y-m-d H:m:s', $t);
	}

	protected function chooseOptionFrom($options, $chance_null = 0)
	{
		if ($chance_null AND mt_rand(1, 100) < $chance_null) return null;
		if (!$options) return null;

		$key = array_rand($options);
		return $options[$key];
	}
}