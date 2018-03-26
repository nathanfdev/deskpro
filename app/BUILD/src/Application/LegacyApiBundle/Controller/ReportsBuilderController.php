<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Dpql\SqlSelect;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Reports\Builder;
use Application\DeskPRO\Reports\Form\Type\ReportType;
use Application\DeskPRO\Reports\ReportEdit;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class ReportsBuilderController extends AbstractController
{
    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        return $this->createApiResponse([
            'reports' => $reportsBuilder->getAll(),
        ]);
    }

    //###################################################################################################################
    // list custom reports
    //###################################################################################################################

    public function listCustomAction()
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        return $this->createApiResponse([
            'reports' => $reportsBuilder->getCustomReports(),
        ]);
    }

    //###################################################################################################################
    // list built-in reports
    //###################################################################################################################

    public function listBuiltInAction()
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        return $this->createApiResponse([
            'reports' => $reportsBuilder->getBuiltInReports(),
        ]);
    }

    //###################################################################################################################
    // get group params
    //###################################################################################################################

    public function getGroupParamsAction()
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        return $this->createApiResponse($reportsBuilder->getGroupParams());
    }

    //###################################################################################################################
    // get report
    //###################################################################################################################

    public function getAction($id)
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');
        $report         = $reportsBuilder->getById($id);

        if (!$report) {
            throw $this->createNotFoundException();
        }

        $rendered_result = $reportsBuilder->getRenderedResult($id);
        $query_parts     = $reportsBuilder->getQueryParts($id, false);

        return $this->createApiResponse([
            'rendered_result' => $rendered_result,
            'query_parts'     => $query_parts,
            'report'          => $this->getApiData($report),
            'type'            => $report->is_custom ? 'custom' : 'builtIn',
        ]);
    }

    //###################################################################################################################
    // save report
    //###################################################################################################################

    public function saveAction($id)
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        if ($id) {
            $report = $reportsBuilder->getById($id);
            if (!$report) {
                throw $this->createNotFoundException();
            }
        } else {
            $report = $reportsBuilder->createNew();
        }

        if (!$report->is_custom) {
            throw ValidationException::create('you can edit only custom report');
        }

        if ($error = $reportsBuilder->getErrors($id, 'from_request')) {
            return $this->createApiResponse(['error' => $error]);
        } else {
            $postData    = $this->in->getAll('req');
            $report_edit = new ReportEdit($report);

            $form = $this->createForm(new ReportType(), $report_edit, ['cascade_validation' => true]);
            $form->submit($this->deleteExtraDataFromRequest($form, $postData, 'report'), true);

            if ($form->isValid()) {
                $report_edit->save($this->em);
            } else {
                return $this->createApiValidationErrorResponse(
                    $this->container->getValidator()->validate($report)
                );
            }

            $reportsBuilder->saveQuery($report, 'from_request');
            $rendered_result = $reportsBuilder->getRenderedResult($id, 'from_request');

            return $this->createApiResponse([
                'success'         => true,
                'id'              => $report->id,
                'rendered_result' => $rendered_result,
            ]);
        }
    }

    //###################################################################################################################
    // clone report
    //###################################################################################################################

    public function cloneAction($id)
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');
        $report         = $reportsBuilder->getById($id);

        if (!$report) {
            throw $this->createNotFoundException();
        }

        $new_report              = $reportsBuilder->createNew();
        $new_report->title       = $this->in->getString('title') ?: $report->title;
        $new_report->description = $this->in->getString('description') ?: $report->description;
        $new_report->query       = $report->query;

        $parts = $this->in->getArrayValue('parts');
        if ($parts) {
            $query = Display::getQueryStringFromParts($parts);
            if ($query) {
                $new_report->query = $query;
            }
        }

        $this->em->getConnection()->beginTransaction();

        try {
            $this->em->persist($new_report);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $this->createApiResponse([
            'success' => true,
            'id'      => $new_report->id,
        ]);
    }

    //###################################################################################################################
    // delete report
    //###################################################################################################################

    public function deleteAction($id)
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');
        $report         = $reportsBuilder->getById($id);

        if (!$report) {
            throw $this->createNotFoundException();
        }

        if (!$report->is_custom) {
            throw ValidationException::create('you can delete only custom report');
        }

        $reportsBuilder->remove($report);

        return $this->createSuccessResponse(['id' => $id]);
    }

    //###################################################################################################################
    // test report
    //###################################################################################################################

    public function testAction($id)
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        if ($error = $reportsBuilder->getErrors($id, 'from_request')) {
            return $this->createApiResponse([
                'error' => $error,
                'sql'   => SqlSelect::getLastCompiledSql(),
            ]);
        } else {
            $rendered_result = $reportsBuilder->getRenderedResult($id, 'from_request');

            return $this->createApiResponse([
                'rendered_result' => $rendered_result,
                'sql'             => SqlSelect::getLastCompiledSql(),
            ]);
        }
    }

    //###################################################################################################################
    // parse
    //###################################################################################################################

    public function parseAction()
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        return $this->createApiResponse($reportsBuilder->parseInput());
    }

    //###################################################################################################################
    // download
    //###################################################################################################################

    public function downloadAction($id, $type)
    {
        /** @var Builder $reportsBuilder */
        $reportsBuilder = $this->container->getSystemService('reports_builder');

        return $reportsBuilder->outputDownloadContent($id, $type);
    }
}
