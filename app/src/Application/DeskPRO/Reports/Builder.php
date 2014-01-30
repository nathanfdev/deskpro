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

use Doctrine\ORM\EntityManager;

class Builder
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\EntityRepository\ReportBuilder
	 */
	protected $repository;

	public function __construct(EntityManager $em)
	{
		$this->em         = $em;
		$this->repository = $this->em->getRepository('DeskPRO:ReportBuilder');
	}


	/**
	 * @return array
	 */

	public function getAll()
	{
		return array(
			'customReports'  => $this->repository->getCustomReports(),
			'builtInReports' => $this->repository->getBuiltInReports(),
		);
	}


	/**
	 * @param array $params
	 * @return array
	 */
	protected function mergeReportBuilderLayoutParams(array $params = array())
	{
		$grouped   = $this->repository->groupReportsList();
		$favorites = $this->repository->getFavoritesForPerson();

		$reportBuilderParams = array(
			'customReports'     => $grouped['custom'],
			'builtInReports'    => $grouped['builtIn'],
			'favoriteReports'   => $favorites,
			'favoritesJs'       => $this->repository->getFavoritesSimplified($favorites),
			'reportGroupParams' => $this->repository->getReportGroupParams()
		);

		return array_merge($reportBuilderParams, $params);
	}
}