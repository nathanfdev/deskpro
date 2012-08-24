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
 * @subpackage AdminBundle
 */

namespace Application\ReportBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Compiler;
use Application\DeskPRO\Entity\ReportBuilder;
use Application\DeskPRO\Dpql\Exception AS DpqlException;
use Application\DeskPRO\Dpql\Statement\Display;

class ReportBuilderController extends AbstractController
{
	public function indexAction()
	{
		if (!App::getConfig('enable_report_builder')) {
			return $this->render('ReportBundle:ReportBuilder:index-disabled.html.twig', array());
		}

		return $this->render('ReportBundle:ReportBuilder:index.html.twig', $this->mergeReportBuilderLayoutParams(array(
		)));
	}

	public function queryAction()
	{
		$query = $this->in->getString('query');
		$parts = $this->in->getArrayValue('parts');

		$inputType = $this->in->getString('inputType');
		if ($inputType == 'builder') {
			$query = Display::getQueryStringFromParts($parts);
		}

		$output = $this->in->getString('output');
		if ($output) {
			try {
				return $this->_getReportResponseForType($output, $query, 'DeskPRO Report Builder Query');
			} catch (DpqlException $e) {
				// fall through - an error will be triggered below
			}
		}

		if ($this->in->getBool('save')) {
			$newReport = new ReportBuilder();
			$newReport->query = $query;

			return $this->_getReportEditOutput($newReport);
		}

		$statement = false;
		$results = false;
		$error = false;

		if ($query) {
			$results = $this->renderQuery($query, 'html', $error);
			if (!$error) {
				$compiler = new Compiler();
				$statement = $compiler->compile($query);
				$parts = $this->_getDpqlPartsForInput($statement);
			}
		}

		return $this->render('ReportBundle:ReportBuilder:query.html.twig', $this->mergeReportBuilderLayoutParams(array(
			'query' => $query,
			'parts' => $parts,
			'inputType' => $inputType ?: 'builder',
			'error' => $error,
			'results' => $results,
			'statement' => $statement
		)));
	}

	public function parseAction()
	{
		$parts = $this->in->getArrayValue('parts');
		$query = $this->in->getString('query');

		$currentType = $this->in->getString('currentType');
		$newType = $this->in->getString('newType');

		$results = array();

		if ($currentType == 'builder' && $newType == 'query') {
			$results = array('query' => Display::getQueryStringFromParts($parts));
		} else if ($currentType == 'query' && $newType == 'builder') {
			if (!$query) {
				$results = array('parts' => $this->_getDpqlPartsForInput());
			} else {
				try {
					$compiler = new Compiler();
					$statement = $compiler->compile($query);
					$results = array('parts' => $this->_getDpqlPartsForInput($statement));
				} catch (DpqlException $e) {
					$results = array('error' => $e->getMessage());
				}
			}
		} else {
			$results = array(
				'error' => 'Unknown conversion action.'
			);
		}

		return $this->createJsonResponse($results);
	}

	protected function _getDpqlPartsForInput(Display $statement = null)
	{
		if (!$statement) {
			return array(
				'display' => 'TABLE',
				'select' => '',
				'from' => '',
				'where' => '',
				'splitBy' => '',
				'groupBy' => '',
				'orderBy' => '',
				'limit' => '',
				'offset' => ''
			);
		}

		$parts = $statement->getDpqlParts();

		return array(
			'display' => $parts['DISPLAY'],
			'select' => $parts['SELECT'],
			'from' => $parts['FROM'],
			'where' => $parts['WHERE'],
			'splitBy' => $parts['SPLIT'],
			'groupBy' => $parts['GROUP'],
			'orderBy' => $parts['ORDER'],
			'limit' => $parts['LIMIT'] ?: '',
			'offset' => $parts['OFFSET'] ?: ''
		);
	}

	public function reportAction($report_builder_id)
	{
		$report = $this->getReportOr404($report_builder_id);
		$error = false;

		$query = $this->in->getString('query');
		$parts = $this->in->getArrayValue('parts');
		$run = true;

		$inputType = $this->in->getString('inputType');
		if ($inputType == 'builder') {
			$query = Display::getQueryStringFromParts($parts);
		}
		if (!$query) {
			$query = $report->query;
		}

		$output = $this->in->getString('output');
		if ($output) {
			try {
				return $this->_getReportResponseForType($output, $query, $report->title);
			} catch (DpqlException $e) {
				$run = true;
				// fall through - an error will be triggered below
			}
		}

		if ($this->in->getBool('clone')) {
			$newReport = new ReportBuilder();
			$newReport->title = $report->title;
			$newReport->description = $report->description;
			$newReport->query = $query;
			$newReport->parent = $report;
			if ($this->em->getRepository('DeskPRO:ReportBuilder')->canManageBuiltInReports()) {
				$newReport->is_custom = $report->is_custom;
				$newReport->unique_key = $report->unique_key;
				$newReport->category = $report->category;
			} else {
				$newReport->is_custom = true;
			}

			return $this->_getReportEditOutput($newReport);
		}

		if ($this->in->getBool('save')) {
			$this->ensureRequestToken();

			$this->renderQuery($query, 'html', $error);
			if (!$error) {
				$report->query = $query;

				$this->em->getConnection()->beginTransaction();

				try {
					$this->em->persist($report);
					$this->em->flush();
					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				return $this->redirectRoute('report_builder_report', array('report_builder_id' => $report->id));
			}
		}

		if ($run) {
			$results = $this->renderQuery($query, 'html', $error);
		} else {
			$results = '';
		}

		if (!$error) {
			$compiler = new Compiler();
			$statement = $compiler->compile($query);
			$parts = $this->_getDpqlPartsForInput($statement);
		}

		return $this->render('ReportBundle:ReportBuilder:report.html.twig', $this->mergeReportBuilderLayoutParams(array(
			'report' => $report,
			'run' => $run,
			'query' => $query,
			'parts' => $parts,
			'inputType' => $inputType ?: 'builder',
			'error' => $error,
			'results' => $results
		)));
	}

	public function favoriteAction($report_builder_id)
	{
		$report = $this->getReportOr404($report_builder_id);

		$this->ensureAuthToken('report_builder_favorite', $this->in->getString('token'));

		if ($this->in->getBool('favorite')) {
			$report->addFavoritedPerson(App::getCurrentPerson());
		} else {
			$report->removeFavoritedPerson(App::getCurrentPerson());
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($report);
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		if ($this->request->isXmlHttpRequest()) {
			$rbRepository = $this->em->getRepository('DeskPRO:ReportBuilder');

			$reports = $rbRepository->getAllReports();
			$grouped = $rbRepository->groupReportsList($reports);

			return $this->render('ReportBundle:ReportBuilder:favorite-list.html.twig', array(
				'favorites' => $grouped['favorites']
			));
		}

		return $this->redirectRoute('report_builder_report', array('report_builder_id' => $report->id));
	}

	public function editAction($report_builder_id)
	{
		if ($report_builder_id) {
			$report = $this->getReportOr404($report_builder_id);
			if (!$report->isEditable()) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(
					"This report is not editable."
				);
			}
		} else {
			$report = new ReportBuilder();
		}

		$errors = array();

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken();

			$title = $this->in->getString('title');

			$query = $this->in->getString('query');
			$parts = $this->in->getArrayValue('parts');

			$inputType = $this->in->getString('inputType');
			if ($inputType == 'builder') {
				$query = Display::getQueryStringFromParts($parts);
			}
			if (!$query) {
				$query = $report->query;
			}

			$report->title = $title;
			$report->description = $this->in->getString('description');
			$report->query = $query;

			$uniqueKey = $this->in->getString('unique_key');
			if ($uniqueKey) {
				$report->unique_key = $uniqueKey;
				$report->is_custom = false;
				$report->category = $this->in->getString('category');
				$report->parent = null;
			} else {
				$report->unique_key = null;
				$report->is_custom = true;
				$report->category = null;

				$parentId = $this->in->getInteger('parent_id');
				if ($parentId) {
					$parent = $this->em->getRepository('DeskPRO:ReportBuilder')->find($parentId);
					$report->parent = $parent ?: null;
				} else {
					$report->parent = null;
				}
			}

			if ($title === '') {
				$errors['title'] = 'Please enter a title for this report.';
			}

			if ($query) {
				$this->renderQuery($query, 'html', $dpqlError);
				if ($dpqlError) {
					$errors['query'] = 'There was an error in your query: ' . $dpqlError;
				}
			} else {
				$errors['query'] = 'Please enter a query for this report.';
			}

			if (!$errors) {
				$this->em->getConnection()->beginTransaction();

				try {
					$this->em->persist($report);
					$this->em->flush();
					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				return $this->redirectRoute('report_builder_report', array('report_builder_id' => $report->id));
			}
		}

		return $this->_getReportEditOutput($report, $errors);
	}

	public function deleteAction($report_builder_id)
	{
		$report = $this->getReportOr404($report_builder_id);
		if (!$report->isEditable()) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(
				"This report is not editable."
			);
		}

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken();

			$this->em->beginTransaction();

			try {
				$this->em->remove($report);
				$this->em->flush();
				$this->em->commit();
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}

			return $this->redirectRoute('report_builder');
		}

		return $this->render('ReportBundle:ReportBuilder:delete.html.twig', $this->mergeReportBuilderLayoutParams(array(
			'report' => $report
		)));
	}

	protected function _getReportEditOutput(ReportBuilder $report, array $errors = array())
	{
		try {
			$compiler = new Compiler();
			$statement = $compiler->compile($report->query);
			$parts = $this->_getDpqlPartsForInput($statement);
		} catch (DpqlException $e) {
			$parts = $this->in->getArrayValue('parts');
		}

		$rbRepository = $this->em->getRepository('DeskPRO:ReportBuilder');

		return $this->render('ReportBundle:ReportBuilder:edit.html.twig', $this->mergeReportBuilderLayoutParams(array(
			'report' => $report,
			'parts' => $parts,
			'query' => $report->query,
			'inputType' => $this->in->getString('inputType') ?: 'builder',
			'errors' => $errors,
			'canManageBuiltIn' => $rbRepository->canManageBuiltInReports(),
			'builtInCategories' => $rbRepository->getBuiltInCategories()
		)));
	}

	protected function _getReportResponseForType($type, $query, $title)
	{
		$compiler = new Compiler();
		$renderer = $compiler->compile($query)->getRenderer($type);
		$output = $renderer->render();

		$response = $this->response;
		$response->headers->set('Content-Type', $renderer->getContentType());
		$response->headers->set('Content-Disposition', 'inline; filename=' . $renderer->getFileName($title));
		$response->setContent($output);
		return $response;
	}

	public function renderQuery($query, $renderer, &$error = false) {
		$error = false;
		try {
			$compiler = new Compiler();
			$statement = $compiler->compile($query);
			return $statement->getRenderer($renderer)->render();
		} catch (DpqlException $e) {
			$error = $e->getMessage();
			return false;
		}
	}

	public function getReportOr404($id)
	{
		$report = $this->em->getRepository('DeskPRO:ReportBuilder')->find($id);
		if (!$report) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(
				"There is no report with ID $id"
			);
		}

		return $report;
	}

	public function mergeReportBuilderLayoutParams(array $params)
	{
		$rbRepository = $this->em->getRepository('DeskPRO:ReportBuilder');

		$reports = $rbRepository->getAllReports();
		$grouped = $rbRepository->groupReportsList($reports);

		$reportBuilderParams = array(
			'customReports' => $grouped['custom'],
			'builtInReports' => $grouped['builtIn'],
			'favoriteReports' => $grouped['favorites']
		);

		return array_merge($reportBuilderParams, $params);
	}
}
