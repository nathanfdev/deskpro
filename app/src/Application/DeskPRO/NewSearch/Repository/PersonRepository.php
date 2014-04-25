<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Person Repository
 */
class PersonRepository extends AbstractRepository
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
} 