<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;
use Elastica\Util as ElasticaUtil;

/**
 * Person Repository.
 */
class PersonRepository extends AbstractRepository implements WithLabelsInterface
{
    protected $highlightFields = [
        'name'   => ['fragment_size' => 100],
        'emails' => ['fragment_size' => 100, 'number_of_fragments' => 1],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getQueryFields()
    {
        return ['name', 'first_name', 'last_name', 'emails', 'email_domains', 'phone_numbers', 'custom_data'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryString($q)
    {
        // prepare base query
        $baseQuery = new Query\MultiMatch();
        $baseQuery->setQuery(ElasticaUtil::escapeTerm($q));
        $baseQuery->setFields($this->getQueryFields());
        $baseQuery->setAnalyzer('text_content_analyzer');
        $baseQuery->setOperator('AND');

        // prepare phone number query
        $phoneQuery = new Query\QueryString();
        $phoneQuery->setQuery(preg_replace('#[^0-9]#', '', ElasticaUtil::escapeTerm($q)));
        $phoneQuery->setFields(['phone_numbers']);
        $phoneQuery->setAnalyzer('text_content_analyzer');
        $phoneQuery->setDefaultOperator('AND');

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addShould($baseQuery);
        $boolQuery->addShould($phoneQuery);

        return $boolQuery;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters(array $options = [])
    {
        $mainFilter = new Query\BoolQuery();

        if (isset($options['is_agent'])) {
            $mainFilter->addMust(new Query\Term(['is_agent' => (bool) $options['is_agent']]));
        }
        if (isset($options['with_phone_number']) && $options['with_phone_number']) {
            $mainFilter->addMust(new Query\Exists('phone_numbers'));
        }

        return $mainFilter->toArray();
    }
}
