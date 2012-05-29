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
 * @subpackage
 */

namespace Application\ReportBundle\Controller;

use Orb\Util\Numbers;

class OverviewController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('ReportBundle:Overview:index.html.twig', array(
			'tickets_status_data'            => $this->getValues('tickets_status'),
			'tickets_awaiting_agent_data'    => $this->getValues('tickets_awaiting_agent'),
			'tickets_resolved_data'          => $this->getValues('tickets_resolved'),
			'tickets_response_time_data'     => $this->getValues('tickets_response_time'),
			'tickets_user_waiting_time_data' => $this->getValues('tickets_user_waiting_time'),
			'tickets_opened_hour_data'       => $this->getValues('tickets_opened_hour'),
		));
	}

	public function updateStatAction($type)
	{
		switch ($type) {
			case 'tickets_awaiting_agent':
				$grouping_field = $this->in->getString('grouping_field');
				return $this->render('ReportBundle:Overview:tickets-awaiting-agent.html.twig', array('data' => $this->getValues('tickets_awaiting_agent', array('grouping_field' => $grouping_field))));

			case 'tickets_resolved':
				$grouping_field = $this->in->getString('grouping_field');
				$date_choice = $this->in->getString('date_choice');
				return $this->render('ReportBundle:Overview:tickets-resolved.html.twig', array(
					'data' => $this->getValues('tickets_resolved', array('grouping_field' => $grouping_field, 'date_choice' => $date_choice)))
				);

			case 'tickets_response_time':
				$grouping_field = $this->in->getString('grouping_field');
				$date_choice = $this->in->getString('date_choice');
				return $this->render('ReportBundle:Overview:tickets-response-time.html.twig', array(
					'data' => $this->getValues('tickets_response_time', array('grouping_field' => $grouping_field, 'date_choice' => $date_choice)))
				);

			case 'tickets_user_waiting_time':
				$grouping_field = $this->in->getString('grouping_field');
				return $this->render('ReportBundle:Overview:tickets-user-waiting-time.html.twig', array(
					'data' => $this->getValues('tickets_user_waiting_time', array('grouping_field' => $grouping_field))
				));

			case 'tickets_opened_hour':
				$date_choice = $this->in->getString('date_choice');
				return $this->render('ReportBundle:Overview:tickets-opened-hour.html.twig', array(
					'data' => $this->getValues('tickets_opened_hour', array('date_choice' => $date_choice))
				));

			default:
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Unknown type $type");
		}
	}

	public function getValues($type, array $options = array())
	{
		$options = new \Orb\Util\OptionsArray($options);

		switch ($type) {

			case 'tickets_status':
				$stat = new \Application\ReportBundle\OverviewStat\TicketsStatus();
				$sum = array_sum($stat->getValues());
				return array(
					'titles'         => $stat->getTitles(),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
					'sum'            => $sum,
				);

			case 'tickets_opened_hour':

				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						$date = $this->person->getDateTime();
						$interval = new \DateInterval('P1D');
						$date->sub($interval);
						break;
					case 'this_week':
						$date = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), 1, 1);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y') - 1, 1, 1);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0,0,0);
						break;
				}

				$date2 = $this->person->getDateTime();

				$stat = new \Application\ReportBundle\OverviewStat\TicketsOpenedHour($date, $date2);
				$sum = array_sum($stat->getValues());

				return array(
					'titles'         => $stat->getTitles(),
					'date_choice'    => $options->get('date_choice'),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
					'sum'            => $sum,
				);

			case 'tickets_resolved':
				$date_choice = $options->get('date_choice');
				switch ($date_choice) {
					case 'this_week':
						$date = $this->person->getDateTime();
						$interval = new \DateInterval('P1D');
						$date->sub($interval);
						break;
					case 'this_week':
						$date = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), 1, 1);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y') - 1, 1, 1);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0,0,0);
						break;
				}

				$date2 = $this->person->getDateTime();

				$gf = new \Application\ReportBundle\OverviewStat\GroupingField($options->get('grouping_field', 'department'));
				$stat = new \Application\ReportBundle\OverviewStat\TicketsResolved($gf, $date, $date2);
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
						$date = $this->person->getDateTime();
						$interval = new \DateInterval('P1D');
						$date->sub($interval);
						break;
					case 'this_week':
						$date = $this->person->getDateTime();
						$interval = new \DateInterval('P7D');
						$date->sub($interval);
						break;
					case 'this_month':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y'), 1, 1);
						break;
					case 'this_year':
						$date = $this->person->getDateTime();
						$date->setDate($date->format('Y') - 1, 1, 1);
						break;
					default:
						$options->set('date_choice', 'today');
						$date = $this->person->getDateTime();
						$date->setTime(0,0,0);
						break;
				}

				$date2 = $this->person->getDateTime();

				if ($options->get('grouping_field')) {
					$gf = new \Application\ReportBundle\OverviewStat\GroupingField($options->get('grouping_field'));
				} else {
					$gf = null;
				}
				$stat = new \Application\ReportBundle\OverviewStat\TicketsResponseTime($gf, $date, $date2);

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
					$gf = new \Application\ReportBundle\OverviewStat\GroupingField($options->get('grouping_field'));
				} else {
					$gf = null;
				}
				$stat = new \Application\ReportBundle\OverviewStat\TicketsUserWaitingTime($gf);

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
				$gf = new \Application\ReportBundle\OverviewStat\GroupingField($options->get('grouping_field', 'department'));
				$stat = new \Application\ReportBundle\OverviewStat\TicketsAwaitingAgent($gf);
				$sum = array_sum($stat->getValues());
				return array(
					'grouping_field' => $options->get('grouping_field', 'department'),
					'titles'         => $stat->getTitles(),
					'values'         => $stat->getValues(),
					'max'            => $stat->getMax(),
					'sum'            => $sum,
				);

			default:
				throw new \InvalidArgumentException("Invalid type: $type");
		}
	}
}