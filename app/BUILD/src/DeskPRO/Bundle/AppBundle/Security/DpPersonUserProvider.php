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

namespace DeskPRO\Bundle\AppBundle\Security;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\BanEmail;
use Application\DeskPRO\EntityRepository\Person as PersonRepo;
use Application\DeskPRO\People\PersonGuest;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class DpPersonUserProvider.
 */
class DpPersonUserProvider implements UserProviderInterface
{
    /**
     * @var \Application\DeskPRO\EntityRepository\Person
     */
    private $personRepo;

    /**
     * @var \Application\DeskPRO\EntityRepository\BanEmail
     */
    private $banEmailRepo;

    /**
     * @var array
     */
    private $peopleRefs = [];

    /**
     * Constructor.
     *
     * @param PersonRepo $personRepo
     * @param BanEmail   $banEmail
     */
    public function __construct(PersonRepo $personRepo, BanEmail $banEmail)
    {
        $this->personRepo   = $personRepo;
        $this->banEmailRepo = $banEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        // username is actually the person id here
        if ($person = $this->fetchPerson($username)) {
            return $person;
        }

        // the off chance the email slips by (never expected)
        $person = $this->personRepo->findOneByEmail($username);

        if (!$person) {
            throw new UsernameNotFoundException(sprintf('User %s was not found', $username));
        }

        return $person;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Person) {
            throw new \InvalidArgumentException('the DpPersonUserProvider requires a Person instance for the UserInterface');
        }

        $person = $this->fetchPerson($user->getId());

        return $person ?: new PersonGuest();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === Person::class;
    }

    /**
     * @param $id
     *
     * @return Person|null
     */
    protected function fetchPerson($id)
    {
        // subrequests or esi calls may reload the user from this provider multiple times. we'll use the same ref.
        if (array_key_exists($id, $this->peopleRefs)) {
            return $this->peopleRefs[$id];
        }

        // load the user with usergroups in one query. the usergroups are always going to be needed in permissions layer.
        $qb = $this->personRepo->createQueryBuilder('p')
            ->addSelect('ug')
            ->leftJoin('p.usergroups', 'ug')
            ->where('p.id = :id')
            ->setParameter('id', $id);

        $person = $qb->getQuery()->getOneOrNullResult();

        if ($person) {
            $this->peopleRefs[$id] = $person;
        }

        return $person;
    }

    /**
     * @param Person $person
     *
     * @return null|string
     */
    public function personHasBannedEmail(Person $person)
    {
        foreach ($person->emails as $email) {
            if ($this->banEmailRepo->isEmailBanned($email->email)) {
                return $email->email;
            }
        }

        return null;
    }
}
