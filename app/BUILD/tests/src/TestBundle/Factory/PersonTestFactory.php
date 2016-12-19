<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
