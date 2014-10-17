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

namespace Application\ApiBundle\Controller;

class ReportsOverviewController extends AbstractController
{
	####################################################################################################################
	# get data (for specified type)
	####################################################################################################################

	public function getDataAction($type)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Overview $reports_overview
		 */

		$reports_overview = $this->container->getSystemService('reports_overview');
		$reports_overview->setPerson($this->person);

		return $this->createApiResponse($reports_overview->getOverviewData($type));
	}


	####################################################################################################################
	# get statistics (for specified type)
	####################################################################################################################

	public function getStatsAction($type)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Overview $reports_overview
		 */

		$reports_overview = $this->container->getSystemService('reports_overview');
		$reports_overview->setPerson($this->person);

		$grouping_field = $this->in->getString('grouping_field');
		$options        = array(
			'date_choice' => $this->in->getString('date_choice'),
			'sla_id'      => $this->in->getString('sla_id'),
		);

		try {
			return $this->createApiResponse($reports_overview->getStats($type, $grouping_field, $options));
		} catch(\InvalidArgumentException $e) {
			return $this->createApiResponse($reports_overview->getStats($type, 'department'));
		}
	}
}