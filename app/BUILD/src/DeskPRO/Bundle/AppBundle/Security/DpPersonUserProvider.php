<?php

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
