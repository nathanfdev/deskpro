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
	 * Gets all reports, including the favorited status for the current person
	 *
	 * @return \Application\DeskPRO\Entity\ReportBuilder[]
	 */
	public function getAllReports()
	{
		$person = App::getCurrentPerson();

		$results = $this->getEntityManager()->createQuery('
			SELECT rb AS report, p.id
			FROM DeskPRO:ReportBuilder rb
			LEFT JOIN rb.favorited_by p WITH p.id = :person_id
			ORDER BY rb.title
		')->execute(array('person_id' => $person->id));

		$output = array();
		foreach ($results AS $result) {
			$report = $result['report'];
			$report->setFavoritedStatus($person, $result['id'] !== null);

			$output[] = $report;
		}

		return $output;
	}

	/**
	 * Groups a list of reports for use in the reports list. Returns lists of
	 * reports in these keys:
	 *  - favorite: list of favorite reports for the current person
	 *  - custom: list of custom reports
	 *  - builtIn: grouped list of built-in reports. Grouped by printable name of the group.
	 *
	 * @param array $reports
	 * @return array
	 */
	public function groupReportsList(array $reports)
	{
		$favorites = array();
		$custom = array();
		$builtIn = array();
		$categories = $this->getBuiltInCategories();

		foreach ($reports AS $report) {
			if ($report->isFavorited()) {
				$favorites[] = $report;
			}
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
			'favorites' => $favorites,
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
			'' => 'Other',
		);
	}

	/**
	 * @return boolean
	 */
	public function canManageBuiltInReports()
	{
		return (bool)App::getConfig('debug.dev');
	}
}
