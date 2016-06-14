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

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Person.
 */
class Person extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $contact_data = new Entity\PersonContactData();
        $contact_data
            ->setContactType('fax')
            ->setComment('some comment')
        ;

        $person = new Entity\Person();
        $person->setEmail('user@example.com');
        $person->setName('Old name');
        $person->addContactData($contact_data);

        $manager->persist($person);
        $manager->flush();

        foreach (array('label1', 'old label 1', 'old label 2') as $label) {
            $label_entity = new Entity\LabelPerson();
            $label_entity->setLabel($label);

            $person->addLabel($label_entity);
            $manager->persist($label_entity);
        }

        $manager->persist($person);

        $person = new Entity\Person();
        $person->setEmail('user2@example.com');
        $person->setName('Old name 2');
        $person->addContactData($contact_data);

        $manager->persist($person);
        $manager->flush();

        foreach (array('label1', 'label2') as $label) {
            $label_entity = new Entity\LabelPerson();
            $label_entity->setLabel($label);

            $person->addLabel($label_entity);
            $manager->persist($label_entity);
        }

        $manager->persist($person);

        $manager->flush();
    }
}
