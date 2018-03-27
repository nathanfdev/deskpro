<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyLimit;
use DeskPRO\Bundle\AppBundle\Limits\Model\AbstractLimit;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultApiFixtures extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $global_hourly_limit = new ApiKeyLimit();
        $limit               = $this->container->get('settings_resolver')->getGlobalSettings()->get('api_limits.global.hour');

        if ($limit != -1) {
            $global_hourly_limit->setLimit($limit)
                ->setCurrent($limit)
                ->setInterval(3600)
                ->setType(AbstractLimit::TYPE_GLOBAL);
            $manager->persist($global_hourly_limit);
        }

        $global_daily_limit = new ApiKeyLimit();
        $limit              = $this->container->get('settings_resolver')->getGlobalSettings()->get('api_limits.global.day');

        if ($limit != -1) {
            $global_daily_limit->setLimit($limit)
                ->setCurrent($limit)
                ->setInterval(86400)
                ->setType(AbstractLimit::TYPE_GLOBAL);
            $manager->persist($global_daily_limit);
        }

        $manager->flush();
    }
}
