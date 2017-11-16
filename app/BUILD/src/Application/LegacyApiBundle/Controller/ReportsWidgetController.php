<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\EntityRepository\ReportWidget as ReportWidgetRepository;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Reports\ReportsWidgetService;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ApiModes("all")
 */
class ReportsWidgetController extends AbstractController
{
    /**
     * @return Response
     */
    public function listAction()
    {
        return $this->createApiResponse($this->getReportsAndLabels());
    }

    /**
     * @return array
     */
    protected function getReportsAndLabels()
    {
        /** @var ReportWidgetRepository $repository */
        $repository = $this->em->getRepository(ReportWidget::class);
        $reports    = $repository->getAllReports();
        $apiData    = [
            'reports' => [],
            'labels'  => [],
        ];

        $translator = $this->container->getTranslator();
        $i          = 0;
        foreach ($reports as $report) {
            $datum            = $report->toApiData();
            $translatedLabels = [];
            foreach ($datum['labels'] as $label) {
                $phraseName      = 'reports.labels.'.strtolower($label);
                $translatedLabel = $translator->hasPhrase($phraseName)
                    ? $translator->phrase($phraseName)
                    : ucfirst($label);
                $translatedLabels[]        = $translatedLabel;
                $apiData['labels'][$label] = [
                    'id'    => ++$i,
                    'label' => $translatedLabel,
                    'value' => ucfirst($label),
                ];
            }
            $datum['labels']      = $translatedLabels;
            $apiData['reports'][] = $datum;
        }

        $apiData['labels'] = array_values($apiData['labels']);

        return $apiData;
    }

    /**
     * @return Response
     */
    public function getGroupParamsAction()
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');

        return $this->createApiResponse($reportsWidget->getGroupParams());
    }

    /**
     * @param int $id
     *
     * @return Response
     */
    public function getAction($id)
    {
        return $this->createApiResponse($this->getReportWidgetData($id));
    }

    private function getReportWidgetData($id, $useRequest = false)
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');
        $report        = $reportsWidget->getById($id);
        if (!$report) {
            throw $this->createNotFoundException();
        }
        $queryParts = $reportsWidget->getQueryParts($id, false);
        $widget     = $this->getApiData($report);
        $translator = $this->container->getTranslator();
        foreach ($widget['labels'] as &$label) {
            $phraseName = 'reports.labels.'.strtolower($label);
            $label      = $translator->hasPhrase($phraseName) ? $translator->phrase($phraseName) : ucfirst($label);
        }
        $widget['query_parts'] = $queryParts;
        if ($useRequest) {
            $reportData            = $this->in->getArrayValue('report');
            $widget['variables']   = $reportData['variables'];
            $widget['query_parts'] = $this->in->getArrayValue('parts');
        }

        return $widget;
    }

    /**
     * @param int $id
     *
     * @throws ValidationException
     *
     * @return Response
     */
    public function saveAction($id)
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');
        $displayOnly   = $this->in->getBool('displayOnly');
        if ($id) {
            $report = $reportsWidget->getById($id);
            if (!$report) {
                throw $this->createNotFoundException();
            }
        } else {
            $report = $reportsWidget->createNew();
        }
        if (!$report->isCustom()) {
            throw ValidationException::create('you can edit only custom report');
        }
        if ($error = $reportsWidget->getErrors($id, $displayOnly ? false : 'from_request')) {
            return $this->createApiResponse(['error' => $error]);
        } else {
            $postData = $this->in->getAll('req');
            $form     = $this->createForm('form_dashboards_report_widget', $report, ['cascade_validation' => true]);
            $form->submit($postData['report'], true);

            if ($form->isValid()) {
                $this->em->persist($report);
                $this->em->flush();
            } else {
                return $this->createApiValidationErrorResponse(
                    $this->container->getValidator()->validate($report)
                );
            }
            if (!$displayOnly) {
                $reportsWidget->saveQuery($report);
            }

            $apiData = $this->getReportsAndLabels();

            return $this->createApiResponse([
                'success' => true,
                'id'      => $report->getId(),
                'labels'  => $apiData['labels'],
            ]);
        }
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     * @throws \Throwable
     *
     * @return Response
     */
    public function cloneAction($id)
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');
        $report        = $reportsWidget->getById($id);
        if (!$report) {
            throw $this->createNotFoundException();
        }
        $newReport = $reportsWidget->createNew();
        $newReport->setTitle($this->in->getString('title') ?: $report->getTitle());
        $newReport->setDescription($this->in->getString('description') ?: $report->getDescription());
        $newReport->setQuery($report->getQuery());
        $parts = $this->in->getArrayValue('parts');
        if ($parts) {
            $query = Display::getQueryStringFromParts($parts);
            if ($query) {
                $newReport->setQuery($query);
            }
        }
        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($newReport);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        return $this->createApiResponse([
            'success' => true,
            'id'      => $newReport->getId(),
        ]);
    }

    /**
     * @param $id
     *
     * @throws ValidationException
     * @throws \Exception
     * @throws \Throwable
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');
        $report        = $reportsWidget->getById($id);
        if (!$report) {
            throw $this->createNotFoundException();
        }
        if (!$report->isCustom()) {
            throw ValidationException::create('you can delete only custom report');
        }
        $reportsWidget->remove($report);

        return $this->createSuccessResponse(['id' => $id]);
    }

    /**
     * @param $id
     *
     * @throws \Throwable
     *
     * @return Response
     */
    public function testAction($id)
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');
        $parts         = $this->in->getArrayValue('parts');
        $query         = $parts && isset($parts['from']) && $parts['from'] ? 'from_request' : null;
        if ($error = $reportsWidget->getErrors($id, $query)) {
            return $this->createApiResponse(['error' => $error]);
        } else {
            $renderedResult            = $reportsWidget->getRenderedResult($id, $query);
            $widget                    = $this->getReportWidgetData($id, true);
            $widget['rendered_result'] = $renderedResult;

            return $this->createApiResponse($widget);
        }
    }

    /**
     * @throws \Throwable
     *
     * @return Response
     */
    public function parseAction()
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');

        return $this->createApiResponse($reportsWidget->parseInput());
    }

    /**
     * @param $id
     * @param $type
     *
     * @throws \Throwable
     *
     * @return Response
     */
    public function downloadAction($id, $type)
    {
        /* @var ReportsWidgetService */
        $reportsWidget = $reportsWidget = $this->container->get('reports.widget.service');

        return $reportsWidget->outputDownloadContent($id, $type);
    }
}
