<?php

namespace DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestQueryContext.
 */
class RequestQueryContext
{
    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    public function __construct(QueryBuilder $qb, $alias, Request $request)
    {
        $this->qb      = $qb;
        $this->alias   = $alias;
        $this->request = $request;
    }

    /**
     * @return QueryBuilder
     */
    public function getQb()
    {
        return $this->qb;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
