<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
use Application\DeskPRO\Dpql\Exception as DpqlException;
use Application\DeskPRO\Dpql\Statement\Display;
use Application\DeskPRO\Entity\ReportBuilder;
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
        $this->in         = App::getContainer()->getIn();
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
     * @param  int           $id
     * @return ReportBuilder
     */
    public function getById($id)
    {
        return $this->repository->find($id);
    }


    /**
     * @return \Application\DeskPRO\Entity\ReportBuilder
     */
    public function createNew()
    {
        $report            = ReportBuilder::createReportBuilder();
        $report->is_custom = true;

        return $report;
    }


    /**
     * @param  int         $id
     * @param  string|null $query
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
            $query = $report->query;
        }

        $error   = false;
        $results = $this->renderQuery($query, 'html', $error, $params);

        return $results;
    }


    /**
     * @param  int                                        $id
     * @param  string                                     $type
     * @param  null                                       $query
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
            $query = $report->query;
        }

        return $this->getReportResponseForType($type, $query, $report->getTitle('printable', $params), $params);
    }


    /**
     * @param  int         $id
     * @param  string|null $query
     * @return boolean
     */
    public function getErrors($id, $query = null)
    {
        $report = $this->repository->find($id);
        $params = $this->getParamsInput('params');

        if ($query == 'from_request') {
            $parts = $this->in->getArrayValue('parts');
            $query = Display::getQueryStringFromParts($parts);
        } else {
            $query = $report->query;
        }

        $error   = false;
        $this->renderQuery($query, 'html', $error, $params);

        return $error;
    }


    /**
     * @param  \Application\DeskPRO\Entity\ReportBuilder $report
     * @throws \Exception
     */
    public function saveQuery($report)
    {
        $parts = $this->in->getArrayValue('parts');
        $query = Display::getQueryStringFromParts($parts);

        $report->query = $query;
        $this->em->getConnection()->beginTransaction();

        try {
            $this->em->persist($report);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch(\Exception $e) {
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
        $query = $this->in->getString('query');

        $currentType = $this->in->getString('currentType');
        $newType     = $this->in->getString('newType');

        if ($currentType == 'builder' && $newType == 'query') {
            $results = array('query' => Display::getQueryStringFromParts($parts));

        } elseif ($currentType == 'query' && $newType == 'builder') {
            if (!$query) {
                $results = array('parts' => $this->getDpqlPartsForInput());
            } else {
                try {
                    $compiler  = new Compiler();
                    $statement = $compiler->lexAndParse($query);
                    $results   = array('parts' => $this->getDpqlPartsForInput($statement));
                } catch(DpqlException $e) {
                    $results = array('error' => $e->getMessage());
                }
            }
        } else {
            $results = array(
                'error' => 'Unknown conversion action.'
            );
        }

        return $results;
    }





    /**
     * @param  ReportBuilder $report
     * @throws \Exception
     */
    public function remove($report)
    {
        $this->em->beginTransaction();

        try {
            $this->em->remove($report);
            $this->em->flush();
            $this->em->commit();
        } catch(\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }
    }


    /**
     * @param  int   $id
     * @return array
     */
    public function getQueryParts($id, $with_params = true)
    {
        $parts = array();

        $report = $this->repository->find($id);

        if ($with_params) {
            $params = $this->getParamsInput('params');
        }
        $query  = $report->query;

        try {
            $compiler  = new Compiler();
            if ($with_params) {
                $input = $compiler->replacePlaceholders($query, $params);
            } else {
                $input = $query;
            }
            $statement = $compiler->lexAndParse($input);
            $parts     = $this->getDpqlPartsForInput($statement);
        } catch(\Exception $e) {
        }

        return $parts;
    }


    /**
     * @param  string $name
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
     * @param  Display $statement
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
     * @param  string                                     $type
     * @param  string                                     $query
     * @param  string                                     $title
     * @param  array                                      $params
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getReportResponseForType($type, $query, $title, array $params = array())
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
        $response->headers->set('Content-Disposition', 'inline; filename=' . $renderer->getFileName($title));
        $response->setContent($output);

        return $response;
    }

    /**
     * @param              $query
     * @param              $renderer
     * @param  bool        $error
     * @param  array       $params
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
}
