<?php

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Organization
 * @package DpFixtures\Import
 */
class Organization extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = new Entity\Organization();
        $organization->setName('Some Organization');

        $manager->persist($organization);
        $manager->flush();

        foreach (array('old label 1', 'old label 2') as $label) {
            $label_entity = new Entity\LabelOrganization();
            $label_entity->setLabel($label);

            $organization->addLabel($label_entity);
            $manager->persist($label_entity);
        }

        $manager->persist($organization);
        $manager->flush();
    }
}