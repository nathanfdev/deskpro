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
use Application\DeskPRO\Dpql\Compiler;
use Application\DeskPRO\Entity\ReportBuilder;
use Application\DeskPRO\Dpql\Exception AS DpqlException;
use Application\DeskPRO\Dpql\Statement\Display;

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
	 * @return array
	 */
	public function getCustomReports()
	{
		return $this->repository->getCustomReports();
	}


	/**
	 * @return array
	 */
	public function getBuiltInReports()
	{
		return $this->repository->getBuiltInReports();
	}


	/**
	 * @return array
	 */
	public function getGroupParams()
	{
		return $this->repository->getReportGroupParams();
	}


	/**
	 * @param int $id
	 * @return ReportBuilder
	 */
	public function getById($id)
	{
		return $this->repository->find($id);
	}


	/**
	 * @param int $id
	 * @param string|null $query
	 * @return array
	 */
	public function getRenderedResult($id, $query = null)
	{
		$report = $this->repository->find($id);
		$params = $this->getParamsInput('params');

		if ($query == 'from_request') {
			$parts = App::getContainer()->getIn()->getArrayValue('parts');
			$query = Display::getQueryStringFromParts($parts);
		} else {
			$query = $report->query;
		}

		$error   = false;
		$results = $this->renderQuery($query, 'html', $error, $params);

		return $results;
	}


	/**
	 * @param int $id
	 * @param string|null $query
	 * @return boolean
	 */
	public function getErrors($id, $query = null)
	{
		$report = $this->repository->find($id);
		$params = $this->getParamsInput('params');

		if ($query == 'from_request') {
			$parts = App::getContainer()->getIn()->getArrayValue('parts');
			$query = Display::getQueryStringFromParts($parts);
		} else {
			$query = $report->query;
		}

		$error   = false;
		$results = $this->renderQuery($query, 'html', $error, $params);

		return $error;
	}


	/**
	 * @param int $id
	 * @return array
	 */
	public function getQueryParts($id)
	{
		$parts = array();

		$report = $this->repository->find($id);

		$params = $this->getParamsInput('params');
		$query  = $report->query;

		try {
			$compiler  = new Compiler();
			$input     = $compiler->replacePlaceholders($query, $params);
			$statement = $compiler->lexAndParse($input);
			$parts     = $this->getDpqlPartsForInput($statement);
		} catch(\Exception $e) {
		}

		return $parts;
	}


	/**
	 * @param string $name
	 * @return array
	 */
	protected function getParamsInput($name = 'params')
	{
		if (isset($_REQUEST[$name])) {
			$params = $_REQUEST[$name];
		} else {
			$params = App::getContainer()->getIn()->getRaw($name);
		}

		if (is_array($params)) {
			ksort($params);
		} else if ($params) {
			$newParams = array();
			foreach (explode(',', $params) AS $k => $v) {
				$newParams[$k + 1] = $v;
			}
			$params = $newParams;
		} else {
			$params = array();
		}

		return $params;
	}


	/**
	 * @param Display $statement
	 * @return array
	 */
	protected function getDpqlPartsForInput(Display $statement = null)
	{
		if (!$statement) {
			return array(
				'display' => 'TABLE',
				'select'  => '',
				'from'    => '',
				'where'   => '',
				'splitBy' => '',
				'groupBy' => '',
				'orderBy' => '',
				'limit'   => '',
				'offset'  => ''
			);
		}

		$parts = $statement->getDpqlParts();

		return array(
			'display' => $parts['DISPLAY'],
			'select'  => $parts['SELECT'],
			'from'    => $parts['FROM'],
			'where'   => $parts['WHERE'],
			'splitBy' => $parts['SPLIT'],
			'groupBy' => $parts['GROUP'],
			'orderBy' => $parts['ORDER'],
			'limit'   => $parts['LIMIT'] ? : '',
			'offset'  => $parts['OFFSET'] ? : ''
		);
	}


	/**
	 * @param       $query
	 * @param       $renderer
	 * @param bool  $error
	 * @param array $params
	 * @return bool|string
	 */
	protected function renderQuery($query, $renderer, &$error = false, array $params = array())
	{
		return Display::renderQuery(
			$renderer,
			$query,
			$params,
			$error
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