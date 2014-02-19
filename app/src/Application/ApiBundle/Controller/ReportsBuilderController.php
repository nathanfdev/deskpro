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

use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Reports\Form\Type\ReportType;
use Application\DeskPRO\Reports\ReportEdit;

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
		$query_parts     = $reports_builder->getQueryParts($id);

		if (!$report) {

			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(
			array(
				 'rendered_result' => $rendered_result,
				 'query_parts'     => $query_parts,
				 'report'          => $this->getApiData($report),
				 'type'            => $report->is_custom ? 'custom' : 'builtIn',
			)
		);
	}


	####################################################################################################################
	# save report
	####################################################################################################################

	public function saveAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');

		if ($id) {

			$report = $reports_builder->getById($id);

			if (!$report) {
				throw $this->createNotFoundException();
			}
		} else {

			$report = $reports_builder->createNew();
		}

		if (!$report->is_custom) {
			throw ValidationException::create('you can edit only custom report');
		}

		if ($error = $reports_builder->getErrors($id, 'from_request')) {

			return $this->createApiResponse(
				array(
					 'error' => $error
				)
			);
		} else {

			$postData = $this->in->getAll('post');

			$report_edit = new ReportEdit($report);

			$form = $this->createForm(new ReportType(), $report_edit, array('cascade_validation' => true));
			$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'report'), true);

			if ($form->isValid()) {

				$report_edit->save($this->em);

			} else {

				return $this->createApiValidationErrorResponse(
					$this->container->getValidator()->validate($report)
				);
			}

			$reports_builder->saveQuery($report, 'from_request');
			$rendered_result = $reports_builder->getRenderedResult($id, 'from_request');

			return $this->createApiResponse(
				array(
					 'success'         => true,
					 'id'              => $report->id,
					 'rendered_result' => $rendered_result,
				)
			);
		}
	}


	####################################################################################################################
	# clone report
	####################################################################################################################

	public function cloneAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');
		$report          = $reports_builder->getById($id);

		if (!$report) {

			throw $this->createNotFoundException();
		}

		$new_report              = $reports_builder->createNew();
		$new_report->title       = $report->title;
		$new_report->description = $report->description;
		$new_report->query       = $report->query;

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($new_report);
			$this->em->flush();
			$this->em->getConnection()->commit();
		} catch(\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->createApiResponse(
			array(
				 'success' => true,
				 'id'      => $new_report->id
			)
		);
	}


	####################################################################################################################
	# delete report
	####################################################################################################################

	public function deleteAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');
		$report          = $reports_builder->getById($id);

		if (!$report) {
			throw $this->createNotFoundException();
		}

		if (!$report->is_custom) {
			throw ValidationException::create('you can delete only custom report');
		}

		$reports_builder->remove($report);

		return $this->createSuccessResponse(array('id' => $id));
	}


	####################################################################################################################
	# test report
	####################################################################################################################

	public function testAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');

		if ($error = $reports_builder->getErrors($id, 'from_request')) {

			return $this->createApiResponse(
				array(
					 'error' => $error
				)
			);
		} else {

			$rendered_result = $reports_builder->getRenderedResult($id, 'from_request');

			return $this->createApiResponse(
				array(
					 'rendered_result' => $rendered_result,
				)
			);
		}
	}


	####################################################################################################################
	# parse
	####################################################################################################################

	public function parseAction()
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');

		return $this->createApiResponse($reports_builder->parseInput());
	}


	####################################################################################################################
	# download
	####################################################################################################################

	public function downloadAction($id, $type)
	{
		/**
		 * @var \Application\DeskPRO\Reports\Builder $reports_builder
		 */

		$reports_builder = $this->container->getSystemService('reports_builder');
		$reports_builder->outputDownloadContent($id, $type);
	}
}