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
namespace DeskPRO\Bundle\AppBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\ORM\Doctrine\Populator;

abstract class DpFixture implements FixtureInterface
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * DpFixture constructor.
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    protected function populate(ObjectManager $manager, array $specs)
    {
        $generator = $this->faker;
        $populator = new Populator($generator, $manager);
        foreach ($specs as $spec) {
            if (count($spec) < 2) {
                throw new \Exception('Each data spec within array passed to the populate() must have at least two
                                      elements: entity class and number of fake entries to generate');
            }
            $populator->addEntity(
                $spec[0],
                $spec[1],
                array_key_exists(2, $spec) ? $spec[2] : [],
                array_key_exists(3, $spec) ? $spec[3] : []
            );
        }

        return $populator->execute();
    }
}
