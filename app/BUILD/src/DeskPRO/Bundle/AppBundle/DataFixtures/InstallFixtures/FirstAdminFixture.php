<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\LabelPerson;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

// Note this being OrderedFixtureInterface will cause a harmless notice
// https://github.com/doctrine/data-fixtures/issues/148

class FirstAdminFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $admin = new Person();
        $admin->setName('Admin Admin');
        $admin->addEmailAddressString('admin@example.com');
        $admin->setPassword('pass');
        $admin->is_user      = true;
        $admin->is_confirmed = true;
        $admin->is_agent     = true;
        $admin->can_agent    = true;
        $admin->can_admin    = true;
        $admin->can_billing  = true;
        $admin->can_reports  = true;
        $manager->persist($admin);
        $manager->flush();

        // Marks the admin as incomplete so it
        // can be reset via start wizard
        $label = new LabelPerson();
        $label->setLabel('not_user');
        $admin->addLabel($label);
        $manager->persist($label);
        $manager->flush();

        $this->setReference('admin', $admin);
    }
}
