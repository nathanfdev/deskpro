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

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class PermissionGroupVoter.
 */
class PermissionGroupVoter extends Voter
{
    const VIEW_LIST = 'list';
    const VIEW      = 'view';
    const CREATE    = 'create';
    const MODIFY    = 'modify';
    const DELETE    = 'delete';

    private static $allowByDefault = [
        self::VIEW_LIST,
        self::VIEW,
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PermissionGroupEntityVoterInterface[]
     */
    private $entityVoters = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param array              $entityVoters
     */
    public function __construct(ContainerInterface $container, array $entityVoters = [])
    {
        $this->container    = $container;
        $this->entityVoters = $entityVoters;
    }

    /**
     * @param EntityVoter\PermissionGroupEntityVoterInterface[] $entityVoters
     */
    public function setEntityVoters(array $entityVoters)
    {
        $this->entityVoters = $entityVoters;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof PermissionGroupContext;
    }

    /**
     * {@inheritdoc}
     *
     * @param PermissionGroupContext $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof Person) {
            return false;
        }

        if ($user->isAdmin()) {
            return true; // admin is allmighty, right?
        }

        $entityClass = $subject->getChildClass() ?: $subject->getParentClass();
        if (!isset($this->entityVoters[$entityClass])) {
            if ($user->isAgent() && in_array($attribute, self::$allowByDefault)) {
                return true;
            } else {
                return false;
            }
        }

        $entityVoter = $this->entityVoters[$entityClass];

        if (is_string($entityVoter)) {
            if (class_exists($entityVoter)) {
                $entityVoter = new $entityVoter();
            } else {
                $entityVoter = $this->container->get($entityVoter);
            }

            $this->entityVoters[$entityClass] = $entityVoter;
        }
        if (!$entityVoter instanceof PermissionGroupEntityVoterInterface) {
            throw new \RuntimeException('Expected entity voter to be instance of '.PermissionGroupEntityVoterInterface::class);
        }

        if ($user->isAgent()) {
            $user->loadHelper('Agent');
            $user->loadHelper('AgentTeam');
            $user->loadHelper('AgentPermissions');
            $user->loadHelper('PermissionsManager');

            return $entityVoter->voteOnAttributeForAgent($attribute, $subject, $user);
        } else {
            return $entityVoter->voteOnAttributeForUser($attribute, $subject, $user);
        }
    }
}
