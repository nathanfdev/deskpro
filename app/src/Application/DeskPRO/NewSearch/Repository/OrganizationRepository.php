<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;
use Elastica\Util as ElasticaUtil;

/**
 * Organization Repository
 */
class OrganizationRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * Fields to be highlighted
     *
     * @var array
     */
    protected $highlightFields = array(
        'name' => array('fragment_size' => 100)
    );

    /**
     * @return array
     */
    protected function getQueryFields()
    {
        return array('_all', 'name', 'email_domains');
    }

    /**
     * @param $q
     * @return Query\MultiMatch
     */
    protected function getQueryString($q)
    {
        $multi_match = new Query\MultiMatch();
        $multi_match->setQuery(ElasticaUtil::escapeTerm($q));
        $multi_match->setFields($this->getQueryFields());
        $multi_match->setAnalyzer('standard');
        $multi_match->setOperator('AND');

        return $multi_match;
    }
}
