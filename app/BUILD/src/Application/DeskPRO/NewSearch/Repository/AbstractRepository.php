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

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;
use Elastica\Search;
use Elastica\Util as ElasticaUtil;
use FOS\ElasticaBundle\Repository;
use Orb\Util\Strings;

/**
 * Abstract Repository.
 */
abstract class AbstractRepository extends Repository
{
    const MAX_LEN = 315;

    /**
     * Tag to be placed before highlight.
     *
     * @var string
     */
    protected $highlightPreTag = '<em class="highlight">';

    /**
     * Tab to be placed after highlight.
     *
     * @var string
     */
    protected $highlightPostTag = '</em>';

    /**
     * Fields to be highlighted.
     *
     * @var array
     */
    protected $highlightFields = [];

    /**
     * Find.
     *
     * Prepares an updated query object and passes back to parent function
     * for actual execution.
     *
     * @param string $query
     * @param null   $limit
     * @param array  $options
     *
     * @return array
     */
    public function find($query, $limit = null, $options = [])
    {
        $queryObj = $this->getQuery($query, $options);
        $queryObj->setSize(50);

        if (isset($options['sort_type'])) {
            switch ($options['sort_type']) {
                case 'date_active':
                    $queryObj->setSort([
                        ['date_active' => ['order' => 'desc']],
                        '_score',
                    ]);
                    break;
                case 'date_created':
                    $queryObj->setSort([
                        ['date_created' => ['order' => 'desc']],
                        '_score',
                    ]);
                    break;
            }
        }

        $this->setHighlight($queryObj);

        $options = array_intersect_key($options, array_fill_keys([
            Search::OPTION_SEARCH_TYPE,
            Search::OPTION_ROUTING,
            Search::OPTION_PREFERENCE,
            Search::OPTION_VERSION,
            Search::OPTION_TIMEOUT,
            Search::OPTION_FROM,
            Search::OPTION_SIZE,
            Search::OPTION_SCROLL,
            Search::OPTION_SCROLL_ID,
            Search::OPTION_SEARCH_TYPE_SUGGEST,
            Search::OPTION_SEARCH_IGNORE_UNAVAILABLE,
            Search::OPTION_QUERY_CACHE,
        ], true));

        return parent::find($queryObj, $limit, $options);
    }

    /**
     * Constructs the raw query.
     *
     * @param string $q
     * @param array  $options
     *
     * @return Query
     */
    protected function getQuery($q, array $options = [])
    {
        if (isset($q[self::MAX_LEN])) {
            $q = substr($q, 0, self::MAX_LEN);
        }

        $l = Strings::extractRegexMatch('#^\[(.*?)\]$#', $q);
        if ($l && $this instanceof WithLabelsInterface) {
            $queryString = new Query\QueryString(ElasticaUtil::escapeTerm($l));
            $queryString->setFields(['labels']);
            $queryString->setDefaultOperator('AND');
            $query = new Query([
                'query'       => $queryString->toArray(),
                'post_filter' => $this->getFilters($options),
            ]);
        } else {
            $query = new Query([
                'query'       => $this->getQueryString($q)->toArray(),
                'post_filter' => $this->getFilters($options),
            ]);
        }
        if ($this->getSource()) {
            $query->setSource($this->getSource());
        }

        return $query;
    }

    /**
     * Makes sure a "query" var is formatted for use with QueryString.
     *
     * @param string $q
     *
     * @return string
     */
    protected function escapeQueryStringTerm($q)
    {
        $q = ElasticaUtil::escapeTerm($q);
        $q = str_replace(['AND', 'NOT'], ['and', 'not'], $q);

        return $q;
    }

    /**
     * Constructs the query string.
     *
     * @param $q
     *
     * @return Query\QueryString|Query\MultiMatch
     */
    protected function getQueryString($q)
    {
        if (isset($q[self::MAX_LEN])) {
            $q = substr($q, 0, self::MAX_LEN);
        }

        $term = $this->escapeQueryStringTerm($q);

        // If we have an equal number of quotes, then
        // they are properly balanced and it's valid so we can
        // accept the "phrase" search
        if (substr_count($term, '\\"') % 2 === 0) {
            $term = str_replace('\\"', '"', $term);
        }

        $queryString = new Query\QueryString($term);
        $queryString->setFields($this->getQueryFields());
        $queryString->setAnalyzer('text_content_analyzer');
        $queryString->setDefaultOperator('AND');

        return $queryString;
    }

    /**
     * @return array
     */
    protected function getQueryFields()
    {
        return ['_all'];
    }

    /**
     * Constructs the filters array (override as needed).
     *
     * @param array $options
     *
     * @return array
     */
    protected function getFilters(array $options = [])
    {
        return [];
    }

    protected function getSource()
    {
        return false;
    }

    /**
     * Set any highlight requirement.
     *
     * @param Query $query
     */
    protected function setHighlight(Query $query)
    {
        $query->setHighlight([
            'fields'    => $this->highlightFields,
            'pre_tags'  => [$this->highlightPreTag],
            'post_tags' => [$this->highlightPostTag],
        ]);
    }
}
