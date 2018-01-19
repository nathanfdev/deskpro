<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class AdditionalUserGroupsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 40;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $group = new Usergroup();
        $group
            ->setTitle('VIP')
            ->setNote('VIP: '.$this->faker->sentence());
        $manager->persist($group);

        $group = new Usergroup();
        $group
            ->setTitle('Extra Priv')
            ->setNote('Extra Priv: '.$this->faker->sentence());
        $manager->persist($group);

        $group = new Usergroup();
        $group
            ->setTitle('Beta Users')
            ->setNote('Beta Users: '.$this->faker->sentence());
        $manager->persist($group);

        $manager->flush();
    }
}
