<?php

namespace Application\DeskPRO\NewSearch\Repository;

/**
 * Organization Repository
 */
class OrganizationRepository extends AbstractRepository
{
    /**
     * Fields to be highlighted
     *
     * @var array
     */
    protected $highlightFields = array(
        'name' => array('fragment_size' => 100)
    );
} 