<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;
use Elastica\Query\QueryString;
use FOS\ElasticaBundle\Repository;

/**
 * Abstract Repository
 */
abstract class AbstractRepository extends Repository
{
    /**
     * Tag to be placed before highlight
     *
     * @var string
     */
    protected $highlightPreTag = '<em class="highlight">';

    /**
     * Tab to be placed after highlight
     *
     * @var string
     */
    protected $highlightPostTag = '</em>';

    /**
     * Fields to be highlighted
     *
     * @var array
     */
    protected $highlightFields = array();

    /**
     * Find
     *
     * Prepares an updated query object and passes back to parent function
     * for actual execution.
     *
     * @param $query
     * @param null $limit
     * @param array $options
     *
     * @return array
     */
    public function find($query, $limit = null, $options = array())
    {
        $queryObj = $this->getQuery($query);
        $this->setHighlight($queryObj);

        return parent::find($queryObj, $limit, $options);
    }

    /**
     * Constructs the raw query
     *
     * @param $q
     * @return Query
     */
    protected function getQuery($q)
    {
        $query = new Query(array(
            'query' => array(
                'filtered' => array(
                    'query'  => $this->getQueryString($q),
                    'filter' => $this->getFilters(),
                )
            )
        ));

        return $query;
    }

    /**
     * Constructs the query string
     *
     * @param $q
     * @return array
     */
    protected function getQueryString($q)
    {
        $queryString = new QueryString($q);
        $queryString->setDefaultOperator('AND');

        return $queryString->toArray();
    }

    /**
     * Constructs the filters array (override as needed)
     *
     * @return array
     */
    protected function getFilters()
    {
        return array();
    }

    /**
     * Set any highlight requirement
     *
     * @param Query $query
     */
    protected function setHighlight(Query $query)
    {
        $query->setHighlight(array(
            'fields'    => $this->highlightFields,
            'pre_tags'  => array($this->highlightPreTag),
            'post_tags' => array($this->highlightPostTag)
        ));
    }
}