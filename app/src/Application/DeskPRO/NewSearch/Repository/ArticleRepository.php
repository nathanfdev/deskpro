<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Article Repository
 */
class ArticleRepository extends AbstractRepository
{
    /**
     * Fields to be highlighted
     *
     * @var array
     */
    protected $highlightFields = array(
        'title' => array('fragment_size' => 100)
    );

    protected function getQueryFields()
    {
        return array('title', 'labels', 'content');
    }
}
