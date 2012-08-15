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

class ReportBuilderController extends AbstractController
{
	public function indexAction()
	{
		if (!App::getConfig('enable_report_builder')) {
			return $this->render('ReportBundle:ReportBuilder:index-disabled.html.twig', array());
		}

		$statement = false;
		$rendered = false;
		$error = false;

		$query = $this->in->getString('query');
		if ($query) {
			$rendered = $this->renderQuery($query, 'html', $error);
		}

		return $this->render('ReportBundle:ReportBuilder:index.html.twig', $this->mergeReportBuilderLayoutParams(array(
			'query' => $query,
			'error' => $error,
			'rendered' => $rendered,
			'statement' => $statement
		)));
	}

	public function reportAction($report_builder_id)
	{
		$report = $this->getReportOr404($report_builder_id);
		$error = false;

		$query = $this->in->getString('query');
		if (!$query) {
			$query = $report->query;
		}

		if ($this->in->getBool('clone')) {
			$newReport = new ReportBuilder();
			$newReport->title = $report->title;
			$newReport->description = $report->description;
			$newReport->query = $query;
			$newReport->parent = $report;
			$newReport->is_custom = 1;

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

		if ($this->in->getBool('run')) {
			$results = $this->renderQuery($query, 'html', $error);
		} else {
			$results = '';
		}

		return $this->render('ReportBundle:ReportBuilder:report.html.twig', $this->mergeReportBuilderLayoutParams(array(
			'report' => $report,
			'query' => $query,
			'error' => $error,
			'results' => $results
		)));
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

			$report->title = $title;
			$report->description = $this->in->getString('description');
			$report->query = $query;

			if ($title === '') {
				$errors['title'] = 'Please enter a title for this report.';
			}

			$parentId = $this->in->getInteger('parent_id');
			if ($parentId) {
				$parent = $this->em->getRepository('DeskPRO:ReportBuilder')->find($parentId);
				$report->parent = $parent ?: null;
			} else {
				$report->parent = null;
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

	protected function _getReportEditOutput(ReportBuilder $report, array $errors = array())
	{
		return $this->render('ReportBundle:ReportBuilder:edit.html.twig', $this->mergeReportBuilderLayoutParams(array(
			'report' => $report,
			'errors' => $errors
		)));
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

		$reportBuilderParams = array(
			'customReports' => $rbRepository->getCustomReports()
		);

		return array_merge($reportBuilderParams, $params);
	}
}
