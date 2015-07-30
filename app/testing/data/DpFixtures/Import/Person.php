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
        $person = new Entity\Person();
        $person->setEmail('user@example.com');
        $person->setName('Old name');

        $manager->persist($person);
        $manager->flush();

        foreach (array('old label 1', 'old label 2') as $label) {
            $label_entity = new Entity\LabelPerson();
            $label_entity->setLabel($label);

            $person->addLabel($label_entity);
            $manager->persist($label_entity);
        }

        $manager->persist($person);
        $manager->flush();
    }
}
