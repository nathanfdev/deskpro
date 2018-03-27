<?php

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\Factory;

use Doctrine\ORM\EntityManager;
use DpTestSrc\TestBundle\Factory\Builder\PersonTestBuilder;
use Faker\Generator;

/**
 * This is a service available in tests that lets you easily create a person.
 */
class PersonTestFactory
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(EntityManager $em, Generator $faker)
    {
        $this->em    = $em;
        $this->faker = $faker;
    }

    /**
     * Returns a basic user as a new Person, already flushed to the database.
     * They are "invalid" because they have not validated their email yet.
     *
     * @param null $email
     * @param null $name
     *
     * @return \Application\DeskPRO\Entity\Person
     */
    public function createNewInvalidUser($email = null, $name = null, $password = null, $flush = true)
    {
        return $this->getBuilder()->createNew($email, $name, $password)->getPerson($flush);
    }

    public function getBuilder()
    {
        return new PersonTestBuilder($this->em, $this->faker);
    }
}
