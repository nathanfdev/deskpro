<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AuthBundle\Security;

use Application\DeskPRO\EntityRepository\Person as PersonRepo;
use Application\DeskPRO\Entity\Person;
use Orb\Auth\Identity;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DpPersonUserProvider implements UserProviderInterface
{
    /**
     * @var \Application\DeskPRO\EntityRepository\Person
     */
    private $person_repo;

    /**
     * @var array
     */
    private $people_refs;

    public function __construct(PersonRepo $person_repo)
    {
        $this->people_refs = array();
        $this->person_repo = $person_repo;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        // username is actually the person id here
        if ($person = $this->fetchPerson($username)) {
            return $person;
        }

        // the off chance the email slips by (never expected)
        return $this->person_repo->findOneByEmail($username);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Person) {
            throw new \InvalidArgumentException('the DpPersonUserProvider requires a Person instance for the UserInterface');
        }

        $person = $this->fetchPerson($user->getId());

        return $person;
    }

    protected function fetchPerson($id)
    {
        // subrequests or esi calls may reload the user from this provider multiple times. we'll use the same ref.
        if (array_key_exists($id, $this->people_refs)) {
            return $this->people_refs[$id];
        }

        // load the user with usergroups in one query. the usergroups are always going to be needed in permissions layer.
        $qb = $this->person_repo->createQueryBuilder('p')
            ->addSelect('ug')
            ->leftJoin('p.usergroups', 'ug')
            ->where('p.id = :id')
            ->setParameter('id', $id);

        $person = $qb->getQuery()->getOneOrNullResult();

        if ($person) {
            $this->people_refs[$id] = $person;
        }

        return $person;
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Application\DeskPRO\Entity\Person';
    }
}
