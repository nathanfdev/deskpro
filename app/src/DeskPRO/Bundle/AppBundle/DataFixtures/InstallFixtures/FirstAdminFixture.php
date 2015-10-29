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
namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\LabelPerson;
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
        return -10;
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
        $admin->is_user            = true;
        $admin->is_confirmed       = true;
        $admin->is_agent_confirmed = true;
        $admin->is_agent           = true;
        $admin->can_agent          = true;
        $admin->can_admin          = true;
        $admin->can_billing        = true;
        $admin->can_reports        = true;
        $manager->persist($admin);
        $manager->flush();

        // Marks the admin as incomplete so it
        // can be reset via start wizard
        $label = new LabelPerson();
        $label->label = 'not_user';
        $admin->addLabel($label);
        $manager->persist($label);
        $manager->flush();

        $this->setReference('admin', $admin);
    }
}
