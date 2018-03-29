<?php

namespace DeskPRO\Bundle\ApiBundle\Security\Authentication;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class ApiUserProvider.
 */
class ApiUserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if (!$person = $this->em->getRepository(Person::class)->find($username)) {
            return $person;
        }

        throw new UsernameNotFoundException('person with ID "'.$username.'" not found');
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === Person::class;
    }
}
