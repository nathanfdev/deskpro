<?php

namespace Application\DeskPRO\NewSearch\Repository;

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
} 