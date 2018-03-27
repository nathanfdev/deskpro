<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\CustomFields\Handler\Choice;
use Application\DeskPRO\Entity\CustomDefFeedback;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FeedbackCustomDefFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 90;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $customCatDef = new CustomDefFeedback();
        $customCatDef
            ->setSysName('cat')
            ->setTitle('Category')
            ->setDescription('e.g., maybe Windows, Mac, Linux.')
            ->setHandlerClass(Choice::class)
        ;

        $manager->persist($customCatDef);
        $manager->flush();
    }
}
