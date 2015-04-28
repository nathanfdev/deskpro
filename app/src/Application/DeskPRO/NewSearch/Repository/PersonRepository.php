<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;
use Elastica\Util as ElasticaUtil;

/**
 * Person Repository
 */
class PersonRepository extends AbstractRepository implements WithLabelsInterface
{
    /**
     * Fields to be highlighted
     *
     * @var array
     */
    protected $highlightFields = array(
        'name'   => array('fragment_size' => 100),
        'emails' => array('fragment_size' => 100, 'number_of_fragments' => 1)
    );

    /**
     * @return array
     */
    protected function getQueryFields()
    {
        return array('name', 'first_name', 'last_name', 'emails', 'phone_numbers');
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