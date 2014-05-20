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

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\Reports\Overview\ChatGroupingField;
use Application\DeskPRO\Reports\Overview\ChatsCreated;
use Application\DeskPRO\Reports\Overview\GroupingField;
use Application\DeskPRO\Reports\Overview\KbViewsHour;
use Application\DeskPRO\Reports\Overview\TicketsAwaitingAgent;
use Application\DeskPRO\Reports\Overview\TicketSlaStatus;
use Application\DeskPRO\Reports\Overview\TicketsOpenedHour;
use Application\DeskPRO\Reports\Overview\TicketsResolved;
use Application\DeskPRO\Reports\Overview\TicketsResponseTime;
use Application\DeskPRO\Reports\Overview\TicketsStatus;
use Application\DeskPRO\Reports\Overview\TicketsUserWaitingTime;
use Doctrine\ORM\EntityManager;
use Orb\Log\Writer\Stream;
use Orb\Util\OptionsArray;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Overview
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * The currently logged in person.
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	* @var bool
	*/
	protected $no_data_mode = false;

	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger;


	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$logger = new Logger();

		if (dp_get_config('debug.enable_reports_overview_log') && !$this->no_data_mode) {
			$wr = new Stream(dp_get_log_dir() . '/reports-overview.log');
			$wr->enableNewStreamPerWrite();
			$logger->addWriter($wr);
		}

		$this->logger = $logger;
	}


	/**
	 * @param string $type
	 * @return array
	 */
	public function getOverviewData($type)
	{
		$this->person->loadPrefGroup('reports.ui.overview.options');
		return $this->getValues($type);
	}


	/**
	 * @param string $type
	 * @param string $grouping_field
	 * @param array $options
	 * @return array
	 * @throws NotFoundHttpException
	 */
	public function getStats($type, $grouping_field = null, $options = array())
	{
		$this->person->loadPrefGroup('reports.ui.overview.options');

		switch ($type) {

			case 'tickets_awaiting_agent':

				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_awaiting_agent.grouping',
					$grouping_field
				);

				return $this->getValues('tickets_awaiting_agent', array('grouping_field' => $grouping_field));

			case 'tickets_resolved':

				$date_choice = $options['date_choice'];

				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_resolved.grouping',
					$grouping_field
				);
				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_resolved.date_choice',
					$date_choice
				);

				return $this->getValues(
					'tickets_resolved',
					array('grouping_field' => $grouping_field, 'date_choice' => $date_choice)
				);

			case 'tickets_response_time':

				$date_choice = $options['date_choice'];

				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_response_time.grouping',
					$grouping_field
				);
				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_response_time.date_choice',
					$date_choice
				);

				return $this->getValues(
					'tickets_response_time',
					array('grouping_field' => $grouping_field, 'date_choice' => $date_choice)
				);

			case 'tickets_user_waiting_time':

				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_user_waiting_time.grouping',
					$grouping_field
				);

				return $this->getValues('tickets_user_waiting_time', array('grouping_field' => $grouping_field));

			case 'tickets_opened_hour':

				$date_choice = $options['date_choice'];

				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_opened_hour.date_choice',
					$date_choice
				);

				return $this->getValues('tickets_opened_hour', array('date_choice' => $date_choice));

			case 'tickets_sla_status':

				$date_choice = $options['date_choice'];
				$sla_id      = $options['sla_id'];

				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_sla_status.date_choice',
					$date_choice
				);
				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.tickets_sla_status.sla_id',
					$sla_id
				);

				return $this->getValues(
					'tickets_sla_status',
					array('date_choice' => $date_choice, 'sla_id' => $sla_id)
				);

			case 'kb_views_hour':

				return $this->getValues('kb_views_hour');

			case 'chats_created':

				$date_choice = $options['date_choice'];

				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.chats_created.grouping',
					$grouping_field
				);
				$this->em->getRepository('DeskPRO:PersonPref')->savePref(
					$this->person,
					'reports.ui.overview.options.chats_created.date_choice',
					$date_choice
				);

				return $this->getValues(
					'chats_created',
					array('grouping_field' => $grouping_field, 'date_choice' => $date_choice)
				);

			default:
				throw new NotFoundHttpException("Unknown type $type");
		}
	}


	/**
	 * @param Person $person
	 * @return $this
	 */
	public function setPerson(Person $person)
	{
		$this->person = $person;
		return $this;
	}


	/**
	 * @param       $type
	 * @param array $options
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function getValues($type, array $options = array())
	{
		$options = new OptionsArray($options);

		if (!$options->get('grouping_field')) {
			$pref = $this->person->getPref('reports.ui.overview.options.' . $type . '.grouping');
			if ($pref) {
				$options->set('grouping_field', $pref);
			}
		}

		if (!$options->get('date_choice')) {
			$pref = $this->person->getPref('reports.ui.overview.options.' . $type . '.date_choice');
			if ($pref) {
				$options->set('date_choice', $pref);
			}
		}

		switch ($type) {

			case 'tickets_status':
				$stat = new TicketsStatus();
				$stat->setLogger($this->logger);
				$sum = array_sum($stat->getValues());
				return array(
					'titles' => $stat->getTitles(),
					'values' => $stat->getValues(),
					'max'    => $stat->getMax(),
					'sum'    => $sum,
				);

			case 'tickets_opened_hour':

				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						if (date('D') == 'Mon') {
							$date = $this->person->getDateTime()->setTime(0, 0, 0);
						} else {
							$date = $this->person->getDateForTime('last monday')->setTime(0, 0, 0);
						}

						$date_group = 'weekday';
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate((int)$date->format('Y'), (int)$date->format('n'), 1)->setTime(0, 0, 0);
						$date_group = 'day';
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
						$date_group = 'month';
						break;
					default:
						$options->set('date_choice', 'today');
						$date       = $this->person->getDateTime();
						$date_group = 'hour';
						break;
				}

				$date->setTime(0, 0, 0);

				$date2 = new \DateTime();

				if ($this->no_data_mode) {
					return array(
						'date_choice' => $options->get('date_choice')
					);
				}

				$stat = new TicketsOpenedHour($date_group, $date, $date2);
				$stat->setPersonContext($this->person);
				$stat->setLogger($this->logger);
				$sum = array_sum($stat->getValues());

				return array(
					'titles'      => $stat->getTitles(),
					'date_choice' => $options->get('date_choice'),
					'values'      => $stat->getValues(),
					'max'         => $stat->getMax(),
					'sum'         => $sum,
				);

			case 'tickets_resolved':
				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						$date     = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval)->setTime(0, 0, 0);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), (int)$date->format('n'), 1)->setTime(0, 0, 0);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y') - 1, 1, 1)->setTime(0, 0, 0);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0, 0, 0);
						break;
				}

				$date2 = new \DateTime();

				$gf = new GroupingField($options->get(
					'grouping_field',
					'department'
				));

				if ($this->no_data_mode) {
					return array(
						'grouping_field' => $options->get('grouping_field', 'department'),
						'date_choice'    => $options->get('date_choice'),
					);
				}

				$stat = new TicketsResolved($gf, $date, $date2);
				$stat->setLogger($this->logger);
				$sum = array_sum($stat->getValues());
				return array(
					'grouping_field' => $options->get('grouping_field', 'department'),
					'date_choice'    => $options->get('date_choice'),
					'titles'         => $stat->getTitles(),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
					'sum'            => $sum,
				);

			case 'tickets_response_time':
				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						$date     = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval)->setTime(0, 0, 0);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), (int)$date->format('n'), 1)->setTime(0, 0, 0);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y') - 1, 1, 1)->setTime(0, 0, 0);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0, 0, 0);
						break;
				}

				$date2 = new \DateTime();

				if ($options->get('grouping_field')) {
					$gf = new GroupingField($options->get('grouping_field'));
				} else {
					$gf = null;
				}

				if ($this->no_data_mode) {
					return array(
						'grouping_field' => $options->get('grouping_field'),
						'date_choice'    => $options->get('date_choice'),
					);
				}

				$stat = new TicketsResponseTime($gf, $date, $date2);
				$stat->setLogger($this->logger);

				return array(
					'grouping_field' => $options->get('grouping_field'),
					'group_max'      => $stat->getGroupMax(),
					'date_choice'    => $options->get('date_choice'),
					'titles'         => $stat->getTitles(),
					'sub_titles'     => $stat->getSubgroupTitles(),
					'group_keys'     => $stat->getGroupColors(),
					'group_total'    => $stat->getGroupTotal(),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
				);

			case 'tickets_user_waiting_time':
				if ($options->get('grouping_field')) {
					$gf = new GroupingField($options->get('grouping_field'));
				} else {
					$gf = null;
				}
				$stat = new TicketsUserWaitingTime($gf);
				$stat->setLogger($this->logger);

				if ($this->no_data_mode) {
					return array(
						'grouping_field' => $options->get('grouping_field'),
					);
				}

				return array(
					'grouping_field' => $options->get('grouping_field'),
					'group_max'      => $stat->getGroupMax(),
					'titles'         => $stat->getTitles(),
					'sub_titles'     => $stat->getSubgroupTitles(),
					'group_keys'     => $stat->getGroupColors(),
					'group_total'    => $stat->getGroupTotal(),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
				);

			case 'tickets_awaiting_agent':
				$gf = new GroupingField($options->get(
					'grouping_field',
					'department'
				));

				if ($this->no_data_mode) {
					return array(
						'grouping_field' => $options->get('grouping_field', 'department'),
					);
				}

				$stat = new TicketsAwaitingAgent($gf);
				$stat->setLogger($this->logger);
				$sum = array_sum($stat->getValues());
				return array(
					'grouping_field' => $options->get('grouping_field', 'department'),
					'titles'         => $stat->getTitles(),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
					'sum'            => $sum,
				);

			case 'tickets_sla_status':
				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						$date     = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval)->setTime(0, 0, 0);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), (int)$date->format('n'), 1)->setTime(0, 0, 0);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y') - 1, 1, 1)->setTime(0, 0, 0);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0, 0, 0);
						break;
				}

				$date2 = new \DateTime();

				if (!$options->get('sla_id')) {
					$pref = $this->person->getPref('reports.ui.overview.options.' . $type . '.sla_id');
					if ($pref) {
						$options->set('sla_id', $pref);
					}
				}

				$sla_id = $options->get('sla_id', null);
				if ($sla_id) {
					$sla = $this->em->find('DeskPRO:Sla', $sla_id);
					if (!$sla) {
						$sla = null;
					}
				} else {
					$sla_id = null;
				}

				$stat = new TicketSlaStatus($sla_id, $date, $date2);
				$stat->setLogger($this->logger);
				$sum = array_sum($stat->getValues());
				return array(
					'sla_id'      => $sla_id,
					'date_choice' => $date_choice,
					'titles'      => $stat->getTitles(),
					'values'      => $stat->getValues(),
					'max'         => $stat->getMax(),
					'sum'         => $sum,
				);

				break;

			case 'chats_created':
				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						$date     = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval)->setTime(0, 0, 0);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), (int)$date->format('n'), 1)->setTime(0, 0, 0);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), 1, 1)->setTime(0, 0, 0);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0, 0, 0);
						break;
				}

				$date2 = new \DateTime();

				$gf = new ChatGroupingField($options->get(
					'grouping_field',
					'department'
				));

				if ($this->no_data_mode) {
					return array(
						'grouping_field' => $options->get('grouping_field', 'department'),
						'date_choice'    => $options->get('date_choice'),
					);
				}

				$stat = new ChatsCreated($gf, $date, $date2);
				$stat->setLogger($this->logger);
				$sum = array_sum($stat->getValues());
				return array(
					'grouping_field' => $options->get('grouping_field', 'department'),
					'date_choice'    => $options->get('date_choice'),
					'titles'         => $stat->getTitles(),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
					'sum'            => $sum,
				);

			case 'kb_views_hour':

				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						$date     = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval)->setTime(0, 0, 0);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), (int)$date->format('n'), 1)->setTime(0, 0, 0);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y') - 1, 1, 1)->setTime(0, 0, 0);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0, 0, 0);
						break;
				}

				$date2 = new \DateTime();

				if ($this->no_data_mode) {
					return array(
						'date_choice' => $options->get('date_choice'),
					);
				}

				$stat = new KbViewsHour($date, $date2);
				$stat->setLogger($this->logger);
				$sum = array_sum($stat->getValues());

				return array(
					'titles'      => $stat->getTitles(),
					'date_choice' => $options->get('date_choice'),
					'values'      => $stat->getValues(),
					'max'         => $stat->getMax(),
					'sum'         => $sum,
				);

			default:
				throw new \InvalidArgumentException("Invalid type: $type");
		}
	}
}