<?php

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Person
 * @package DpFixtures\Import
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
