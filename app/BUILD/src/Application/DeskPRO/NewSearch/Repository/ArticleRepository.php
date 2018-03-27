<?php

namespace Application\DeskPRO\NewSearch\Repository;

use Elastica\Query;

/**
 * Article Repository.
 */
class ArticleRepository extends AbstractRepository
{
    protected $highlightFields = [
        'title' => ['fragment_size' => 100],
    ];

    /**
     * {@inheritdoc}
     */
    protected function getQueryFields()
    {
        return ['title', 'labels', 'content'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters(array $options = [])
    {
        $mainFilter = new Query\BoolQuery();

        if (empty(array_intersect(['status', 'hidden_status'], array_keys($options)))) {
            $mainFilter->addMustNot(new Query\Term(['hidden_status' => 'deleted']));
        }

        return $mainFilter->toArray();
    }
}
