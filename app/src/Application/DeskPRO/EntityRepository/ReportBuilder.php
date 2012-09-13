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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Util\Numbers;

class ReportBuilder extends AbstractEntityRepository
{
	/**
	 * Gets all reports
	 *
	 * @return \Application\DeskPRO\Entity\ReportBuilder[]
	 */
	public function getAllReports()
	{
		return $this->getEntityManager()->createQuery('
			SELECT rb
			FROM DeskPRO:ReportBuilder rb
			ORDER BY rb.title
		')->execute();
	}

	public function findFavorite(
		\Application\DeskPRO\Entity\ReportBuilder $report,
		\Application\DeskPRO\Entity\Person $person = null,
		array $params = array()
	)
	{
		if (!$person) {
			$person = App::getCurrentPerson();
		}

		ksort($params);

		return $this->getEntityManager()->createQuery('
			SELECT f
			FROM DeskPRO:ReportBuilderFavorite f
			WHERE f.report_builder = ?0 AND f.person = ?1 AND f.params = ?2
		')->setParameters(array($report, $person, $params ? implode(',', $params) : ''))->getOneOrNullResult();
	}

	public function getFavoritesForPerson(\Application\DeskPRO\Entity\Person $person = null)
	{
		if (!$person) {
			$person = App::getCurrentPerson();
		}

		return $this->getEntityManager()->createQuery('
			SELECT f, r
			FROM DeskPRO:ReportBuilderFavorite f
			JOIN f.report_builder r
			WHERE f.person = ?0
			ORDER BY r.title
		')->execute(array($person));
	}

	public function getFavoritesSimplified(array $favorites)
	{
		$output = array();
		foreach ($favorites AS $fav) {
			$output[] = array('id' => $fav->report_builder->id, 'params' => $fav->params);
		}

		return $output;
	}

	/**
	 * Groups a list of reports for use in the reports list. Returns lists of
	 * reports in these keys:
	 *  - custom: list of custom reports
	 *  - builtIn: grouped list of built-in reports. Grouped by printable name of the group.
	 *
	 * @return array
	 */
	public function groupReportsList()
	{
		$reports = $this->getAllReports();

		$custom = array();
		$builtIn = array();
		$categories = $this->getBuiltInCategories();

		foreach ($reports AS $report) {
			if ($report->is_custom) {
				$custom[] = $report;
			} else {
				if (isset($categories[$report->category])) {
					$categoryId = $report->category;
				} else {
					$categoryId = '';
				}
				$builtIn[$categoryId][] = $report;
			}
		}

		$builtInOrdered = array();
		foreach ($categories AS $categoryId => $categoryName)
		{
			if (isset($builtIn[$categoryId])) {
				$builtInOrdered[$categoryName] = $builtIn[$categoryId];
			}
		}

		return array(
			'custom' => $custom,
			'builtIn' => $builtInOrdered
		);
	}

	/**
	 * Gets the list of built-in report grouping categories.
	 *
	 * @return array
	 */
	public function getBuiltInCategories()
	{
		return array(
			'ticket' => 'Tickets',
			'chat' => 'Chats',
			'idea' => 'Ideas',
			'person' => 'People & Organizations',
			'kb' => 'Knowledgebase',
			'news' => 'News',
			'files' => 'Files',
		);
	}

	/**
	 * @return boolean
	 */
	public function canManageBuiltInReports()
	{
		return (bool)App::getConfig('debug.dev');
	}

	public function getFieldGroups()
	{
		return array(
			'tickets' => array(
				'none' => array('none', 'NULL'),
				'department' => array('department', '%s.department'),
				'agent' => array('agent', '%s.agent'),
			)
		);
	}

	public function getDateGroups()
	{
		return array(
			'today' => array('today', '%TODAY%'),
			'this_week' => array('this week', '%THIS_WEEK%'),
			'this_month' => array('this month', '%THIS_MONTH%'),
			'this_year' => array('this year', '%THIS_YEAR%')
		);
	}
}
