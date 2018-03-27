<?php

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
