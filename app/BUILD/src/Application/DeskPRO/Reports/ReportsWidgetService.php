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

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Compiler;
use Application\DeskPRO\Dpql\Exception as DpqlException;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Entity\ReportWidget;
use Application\DeskPRO\EntityRepository\ReportWidget as ReportWidgetRepository;
use Application\DeskPRO\Input\Reader;
use Application\LegacyApiBundle\Service\DashboardWidget;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

class ReportsWidgetService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ReportWidgetRepository
     */
    protected $repository;

    /**
     * @var Reader
     */
    protected $in;

    /**
     * @var DashboardWidget
     */
    protected $dashboardWidget;

    /**
     * ReportsWidgetService constructor.
     *
     * @param EntityManager   $em
     * @param DashboardWidget $dashboardWidget
     */
    public function __construct(EntityManager $em, DashboardWidget $dashboardWidget)
    {
        $this->em              = $em;
        $this->dashboardWidget = $dashboardWidget;
        $this->repository      = $this->em->getRepository(ReportWidget::class);
        $this->in              = App::getContainer()->getIn();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return [
            'customReports'  => $this->repository->getCustomReports(),
            'builtInReports' => $this->repository->getBuiltInReports(),
        ];
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
     *
     * @return ReportWidget
     */
    public function getById($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return ReportWidget
     */
    public function createNew()
    {
        $report = ReportWidget::createReportWidget();
        $report->setIsCustom(true);

        return $report;
    }

    /**
     * @param int    $id
     * @param string $query
     * @param string $format
     *
     * @return array
     */
    public function getRenderedResult($id, $query = null, $format = 'json')
    {
        $report              = $this->repository->find($id);
        $params              = $this->getParamsInput('params');
        $reportData          = $this->in->getArrayValue('report');
        $params['variables'] = $reportData['variables'];


        $resultsStack = [];
        if ($query == 'from_request') {
            $parts = $this->in->getArrayValue('parts');
            $query = Display::getQueryStringFromParts($parts);
        } else {
            $query = $report->getQuery();
        }

        foreach($reportData['display_types'] as $displayType) {
            $renderFormat = $format;
            if ($displayType === DashboardWidget::WIDGET_RENDER_TYPE_TABLE) {
                $renderFormat = 'html';
                if(isset($reportData['jsonTable']) && $reportData['jsonTable'] === true) {
                    $renderFormat = 'json';
                }
            }

            $results = $this->dashboardWidget->renderQuery($query, $params, $displayType, $renderFormat);

            if ($results && $displayType == DashboardWidget::WIDGET_RENDER_TYPE_TABLE && $renderFormat === 'json') {
                $aoColumns = [];
                $columns   = [];
                foreach ($results['columns'] as $column) {
                    $aoColumns[] = null;
                    $columns[]   = ['title' => $column];
                }
                $results['aoColumns'] = $aoColumns;
                $results['columns']   = $columns;
            }
            $resultsStack[] = $results;
        }

        return $resultsStack;
    }

    /**
     * @param int    $id
     * @param string $type
     * @param mixed  $query
     *
     * @return Response
     */
    public function outputDownloadContent($id, $type, $query = null)
    {
        $report = $this->repository->find($id);
        $params = $this->getParamsInput('params');

        if ($query == 'from_request') {
            $parts = $this->in->getArrayValue('parts');
            $query = Display::getQueryStringFromParts($parts);
        } else {
            $query = $report->getQuery();
        }

        return $this->getReportResponseForType($type, $query, $report->getTitle('printable', $params), $params);
    }

    /**
     * @param int    $id
     * @param string $query
     *
     * @return bool
     */
    public function getErrors($id, $query = null)
    {
        $report = $this->repository->find($id);
        $params = $this->getParamsInput('params');

        $reportData          = $this->in->getArrayValue('report');
        $params['variables'] = $reportData['variables'];

        if ($query == 'from_request') {
            $parts = $this->in->getArrayValue('parts');
            $query = Display::getQueryStringFromParts($parts);
        } else {
            $query = $report->getQuery();
        }

        $error = false;
        $this->renderQuery($query, 'html', $error, $params);

        return $error;
    }

    /**
     * @param ReportWidget $report
     *
     * @throws \Exception
     */
    public function saveQuery($report)
    {
        $parts = $this->in->getArrayValue('parts');
        $query = Display::getQueryStringFromParts($parts);

        $report->setQuery($query);
        $this->em->getConnection()->beginTransaction();

        try {
            $this->em->persist($report);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function parseInput()
    {
        $parts = $this->in->getArrayValue('parts');
        $query = $this->in->getStringRaw('query');

        $currentType = $this->in->getString('currentType');
        $newType     = $this->in->getString('newType');

        if ($currentType == 'builder' && $newType == 'query') {
            $results = ['query' => Display::getQueryStringFromParts($parts)];
        } elseif ($currentType == 'query' && $newType == 'builder') {
            if (!$query) {
                $results = ['parts' => $this->getDpqlPartsForInput()];
            } else {
                try {
                    $compiler  = new Compiler();
                    $statement = $compiler->lexAndParse($query);
                    $results   = ['parts' => $this->getDpqlPartsForInput($statement)];
                } catch (DpqlException $e) {
                    $results = ['error' => $e->getMessage()];
                }
            }
        } else {
            $results = [
                'error' => 'Unknown conversion action.',
            ];
        }

        return $results;
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function parseQueryString($query)
    {
        $compiler  = new Compiler();
        $statement = $compiler->lexAndParse($query);
        return $this->getDpqlPartsForInput($statement);
    }

    /**
     * @param ReportWidget $report
     *
     * @throws \Exception
     */
    public function remove($report)
    {
        $this->em->beginTransaction();

        try {
            $this->em->remove($report);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param int  $id
     * @param bool $withParams
     *
     * @return array
     */
    public function getQueryParts($id, $withParams = true)
    {
        $report = $this->repository->find($id);
        $query  = $report->getQuery();

        return $this->compileQueryParts($query, $withParams);
    }

    /**
     * @param $query
     * @param $withParams
     *
     * @return array
     */
    public function compileQueryParts($query, $withParams)
    {
        $parts = [];
        try {
            $compiler = new Compiler();
            if ($withParams) {
                $input = $compiler->replacePlaceholders($query, $this->getParamsInput('params'));

                $params              = $this->getParamsInput('params');
                $reportData          = $this->in->getArrayValue('report');
                $params['variables'] = $reportData['variables'];

                $input = $compiler->replaceVariables($input, $params);
            } else {
                $input = $query;
            }
            $statement = $compiler->lexAndParse($input);
            $parts     = $this->getDpqlPartsForInput($statement);
        } catch (\Exception $e) {
        }

        return $parts;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    protected function getParamsInput($name = 'params')
    {
        if (isset($_REQUEST[$name])) {
            $params = $_REQUEST[$name];
        } else {
            $params = $this->in->getRaw($name);
        }

        if (is_array($params)) {
            ksort($params);
        } elseif ($params) {
            $newParams = [];
            foreach (explode(',', $params) as $k => $v) {
                $newParams[$k + 1] = $v;
            }
            $params = $newParams;
        } else {
            $params = [];
        }

        return $params;
    }

    /**
     * @param Display $statement
     *
     * @return array
     */
    protected function getDpqlPartsForInput(Display $statement = null)
    {
        if (!$statement) {
            return [
                'display'    => 'TABLE',
                'select'     => '',
                'from'       => '',
                'where'      => '',
                'splitBy'    => '',
                'groupBy'    => '',
                'withRollup' => '',
                'orderBy'    => '',
                'limit'      => '',
                'offset'     => '',
            ];
        }

        $parts = $statement->getDpqlParts();

        return [
            'display'    => $parts['DISPLAY'],
            'select'     => $parts['SELECT'],
            'from'       => $parts['FROM'],
            'where'      => $parts['WHERE'],
            'splitBy'    => $parts['SPLIT'],
            'groupBy'    => $parts['GROUP'],
            'orderBy'    => $parts['ORDER'],
            'withRollup' => $parts['WITH_ROLLUP'],
            'limit'      => $parts['LIMIT'] ?: '',
            'offset'     => $parts['OFFSET'] ?: '',
        ];
    }

    /**
     * @param string $type
     * @param string $query
     * @param string $title
     * @param array  $params
     *
     * @return Response
     */
    protected function getReportResponseForType($type, $query, $title, array $params = [])
    {
        @set_time_limit(0);

        $compiler  = new Compiler();
        $statement = $compiler->compile($query, $params);
        $statement->setImplicitLimit(0);

        $renderer = $statement->getRenderer($type);
        $renderer->setTitle($title);
        $output = $renderer->render();

        $response = App::getResponse();
        $response->headers->set('Content-Type', $renderer->getContentType());
        $response->headers->set('Content-Disposition', 'inline; filename='.$renderer->getFileName($title));
        $response->setContent($output);

        return $response;
    }

    /**
     * @param string $query
     * @param string $renderer
     * @param bool   $error
     * @param array  $params
     *
     * @return mixed
     */
    protected function renderQuery($query, $renderer, &$error = false, array $params = [])
    {
        return Display::renderQuery(
            $renderer,
            $query,
            $params,
            $error
        );
    }
}
