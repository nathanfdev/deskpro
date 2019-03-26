<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class BetaFeatureFixture.
 */
class BetaFeatureFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 150;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        if (isset($DP_ENV) && $DP_ENV->getEnvId() === 'test') {
            return;
        }

        /** @var BetaFeatureInterface[] $collection */
        $collection    = $this->container->get('deskpro.features_collection');
        $toggleManager = $this->container->get('deskpro.toggle_feature_manager');

        foreach ($collection as $feature) {
            if ($feature->isEnabledOnInstall()) {
                $toggleManager->enableFeature($feature->getId());
            }
        }
    }
}
