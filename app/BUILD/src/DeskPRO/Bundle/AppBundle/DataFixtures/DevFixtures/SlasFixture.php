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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SlasFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    const NUM_SLAS = 5;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 70;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $batch = [];

        for ($i = 0; $i < self::NUM_SLAS; ++$i) {
            $batch[] = [
                'title'          => $this->faker->word,
                'sla_type'       => $this->faker->randomElement(['first_response', 'resolution', 'waiting_time']),
                'active_time'    => $this->faker->randomElement(['default', 'all', 'work_hours']),
                'work_start'     => 60 * 60 * 10,
                'work_end'       => 60 * 60 * 18,
                'work_days'      => '1,2,3,4,5,6',
                'work_timezone'  => $this->faker->timezone,
                'apply_type'     => $this->faker->randomElement(['all', 'auto', 'manual']),
                'warn_time'      => $this->faker->randomElement([1, 2, 3]),
                'warn_time_unit' => $this->faker->randomElement(['hours', 'days']),
                'fail_time'      => $this->faker->randomElement([1, 2, 3]),
                'fail_time_unit' => $this->faker->randomElement(['hours', 'days']),
            ];
        }
        $this->db->batchInsert(self::TABLE_SLAS, $batch);
    }
}
