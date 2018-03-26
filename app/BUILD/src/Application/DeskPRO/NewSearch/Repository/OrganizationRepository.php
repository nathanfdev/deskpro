<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;
use Elastica\Util as ElasticaUtil;

/**
 * Organization Repository.
 */
class OrganizationRepository extends AbstractRepository implements WithLabelsInterface
{
    protected $highlightFields = [
        'name' => ['fragment_size' => 100],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getQueryFields()
    {
        return ['_all', 'name', 'email_domains'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryString($q)
    {
        $multiMatch = new Query\MultiMatch();
        $multiMatch->setQuery(ElasticaUtil::escapeTerm($q));
        $multiMatch->setFields($this->getQueryFields());
        $multiMatch->setAnalyzer('text_content_analyzer');
        $multiMatch->setOperator('AND');

        return $multiMatch;
    }
}
