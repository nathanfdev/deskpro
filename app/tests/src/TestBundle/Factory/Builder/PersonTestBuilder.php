<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
