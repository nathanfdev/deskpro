<?php

namespace Application\DeskPRO\Reports;

use Application\DeskPRO\App;
use Application\DeskPRO\Dpql\Exception as DpqlException;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Entity\ReportBuilder;
use Doctrine\ORM\EntityManager;

/**
 * Class Builder.
 */
class Builder
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\EntityRepository\ReportBuilder
     */
    protected $repository;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em         = $em;
        $this->repository = $this->em->getRepository(ReportBuilder::class);
        $this->in         = App::getContainer()->getIn();
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
     * @return ReportBuilder
     */
    public function getById($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return ReportBuilder
     */
    public function createNew()
    {
        $report = ReportBuilder::createReportBuilder();
        $report->setIsCustom(true);

        return $report;
    }

    /**
     * @param int         $id
     * @param string|null $query
     *
     * @return array
     */
    public function getRenderedResult($id, $query = null)
    {
        $report = $this->repository->find($id);
        $params = $this->getParamsInput('params');

        if ($query == 'from_request') {
            $parts = $this->in->getArrayValue('parts');
            $query = Display::getQueryStringFromParts($parts);
        } else {
            $query = $report->getQuery();
        }

        $error   = false;
        $results = $this->renderQuery($query, 'html', $error, $params);

        return $results;
    }

    /**
     * @param int    $id
     * @param string $type
     * @param null   $query
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @param int         $id
     * @param string|null $query
     *
     * @return bool
     */
    public function getErrors($id, $query = null)
    {
        $report = $this->repository->find($id);
        $params = $this->getParamsInput('params');

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
     * @param ReportBuilder $report
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
            $this->em->getConnection()->rollback();
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
                    $compiler  = new \Application\DeskPRO\Dpql\Compiler();
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
     * @param ReportBuilder $report
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
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getQueryParts($id, $with_params = true)
    {
        $parts  = [];
        $report = $this->repository->find($id);
        $query  = $report->getQuery();

        try {
            $compiler = new \Application\DeskPRO\Dpql\Compiler();
            if ($with_params) {
                $input = $compiler->replacePlaceholders($query, $this->getParamsInput('params'));
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getReportResponseForType($type, $query, $title, array $params = [])
    {
        @set_time_limit(0);

        $compiler  = new \Application\DeskPRO\Dpql\Compiler();
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
     * @param       $query
     * @param       $renderer
     * @param bool  $error
     * @param array $params
     *
     * @return bool|string
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
