<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class TasksVoter.
 */
class TasksVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return Task::class;
    }
    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        return $user->hasPerm('agent_tasks.use');
    }
    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }
}
