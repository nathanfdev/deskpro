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
        return ['title', 'labels', 'content', 'custom_data', 'custom_data2'];
    }

    public function getQuery($q, array $options = [])
    {
        $query = parent::getQuery($q, $options);

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($query->getQuery());

        $customBool  = new Query\BoolQuery();
        $customMatch = new Query\Match();
        $customMatch
            ->setFieldQuery('custom_data2.value', $q)
            ->setFieldBoost('custom_data2.value', 10);

        $customNested = new Query\Nested();
        $customNested
            ->setPath('custom_data2')
            ->setQuery(
                $customBool
                    ->addMust($customMatch)
            );

        $orQuery = new Query\BoolQuery();
        $orQuery->addShould($boolQuery);
        $orQuery->addShould($customNested);

        $updatedQuery = new Query(
            [
                'query'       => $orQuery->toArray(),
                'post_filter' => $query->getParam('post_filter'),
            ]
        );

        return $updatedQuery;
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
