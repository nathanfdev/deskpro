<?php

namespace Orb\Jira\Entity\Repository;

use Orb\Jira\Repository;

/**
 * Issue Priority Repository
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class PriorityRepository extends Repository
{
    /**
     *
     * @var String Entity Class
     */
    protected $_entityClass = 'Priority';

    /**
     * Entity REST endpoint
     *
     * @var String the REST endpoint
     */
    protected $_endPoint = 'priority';

    /** {@inheritdoc} */
    protected function _create(\Orb\Jira\Entity $entity, \Orb\Jira\Service $client) {}

    /** {@inheritdoc} */
    protected function _update(\Orb\Jira\Entity $entity, \Orb\Jira\Service $client) {}
}
