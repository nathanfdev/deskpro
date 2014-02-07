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

namespace Application\ApiBundle\Controller;

class ReportsBuilderController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');

		return $this->createApiResponse(
			array(
				 'reports' => $reports_builder->getAll()
			)
		);
	}


	####################################################################################################################
	# list custom reports
	####################################################################################################################

	public function listCustomAction()
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');

		return $this->createApiResponse(
			array(
				 'reports' => $reports_builder->getCustomReports()
			)
		);
	}


	####################################################################################################################
	# list built-in reports
	####################################################################################################################

	public function listBuiltInAction()
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');

		return $this->createApiResponse(
			array(
				 'reports' => $reports_builder->getBuiltInReports()
			)
		);
	}


	####################################################################################################################
	# get group params
	####################################################################################################################

	public function getGroupParamsAction()
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');

		return $this->createApiResponse($reports_builder->getGroupParams());
	}


	####################################################################################################################
	# get report
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');
		$report          = $reports_builder->getById($id);
		$rendered_result = $reports_builder->getRenderedResult($id);

		if (!$report) {

			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(
			array(
				 'rendered_result' => $rendered_result,
				 'report'          => $this->getApiData($report),
				 'type'            => $report->is_custom ? 'custom' : 'builtIn',
			)
		);
	}
}