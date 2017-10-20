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
        return ['name', 'first_name', 'last_name', 'emails', 'email_domains', 'phone_numbers'];
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
