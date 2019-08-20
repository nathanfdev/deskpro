<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\CustomFields\Handler\Choice;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CommunityTopicCustomDefFixture.
 */
class CommunityTopicCustomDefFixture extends AbstractDpFixture implements OrderedFixtureInterface
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
        $brands = $manager->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            $customCatDef = new CustomDefCommunityTopic();
            $customCatDef
                ->setBrand($brand)
                ->setSysName('chan')
                ->setTitle('Custom Channel')
                ->setDescription('e.g., maybe Windows, Mac, Linux.')
                ->setHandlerClass(Choice::class)
            ;

            $manager->persist($customCatDef);
        }

        $manager->flush();
    }
}
