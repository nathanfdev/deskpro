<?php

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\Factory\Builder;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManager;
use Faker\Generator;

/**
 * This is a service that exposes fluent methods that let you build a Person,
 * meant to maily be used for making a Person entity in a test case.
 */
class PersonTestBuilder
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Person
     */
    private $person;
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
     * start building a new person. uses faker if you dont supply name/email data.
     *
     * @param null $primary_email
     * @param null $name
     *
     * @return $this
     */
    public function createNew($primary_email = null, $name = null, $password = null)
    {
        $this->person = new Person();

        if ($name === null) {
            $name = $this->faker->name;
        }
        $this->person->name = $name;

        if ($primary_email === null) {
            $primary_email = $this->faker->email;
        }
        $this->person->setEmail($primary_email);

        if ($password) {
            $this->person->setPassword($password);
        }

        return $this;
    }

    /**
     * build the person as an agent.
     *
     * @return $this
     */
    public function makeAgent()
    {
        $this->person->is_agent = true;

        return $this;
    }

    /**
     * @param bool $flush pass false if you don't want to flush the user to the db
     *
     * @return Person
     */
    public function getPerson($flush = true)
    {
        $this->em->persist($this->person);

        if ($flush) {
            $this->em->flush($this->person);
        }

        return $this->person;
    }
}
