<?php

namespace spec\DeskPRO\Bundle\AppBundle\Features;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\AppBundle\Features\FeatureInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Features\FeaturesAccessChecker
 */
class FeaturesAccessCheckerSpec extends ObjectBehavior
{
    public function it_checks_features_availability_in_dev_mode(AppEnv $appEnv)
    {
        $appEnv->isDebug()->willReturn(true);

        $this->beConstructedWith($appEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_EVERYWHERE])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_ONPREM])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_QA])->shouldReturn(true);
    }

    public function it_checks_everywhere_features_availability(AppEnv $appEnv)
    {
        $appEnv->isCloud()->willReturn(false);
        $appEnv->isDebug()->willReturn(false);
        $appEnv->isQa()->willReturn(false);

        $this->beConstructedWith($appEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_EVERYWHERE])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_QA])->shouldReturn(false);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(false);
    }

    public function it_checks_qa_features_availability(AppEnv $appEnv)
    {
        $appEnv->isCloud()->willReturn(false);
        $appEnv->isDebug()->willReturn(false);
        $appEnv->isQa()->willReturn(true);

        $this->beConstructedWith($appEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_QA])->shouldReturn(true);
    }

    public function it_checks_cloud_features_availability(AppEnv $appEnv)
    {
        $appEnv->isCloud()->willReturn(true);
        $appEnv->isDebug()->willReturn(false);
        $appEnv->isQa()->willReturn(false);

        $this->beConstructedWith($appEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_ONPREM])->shouldReturn(false);
    }

    public function it_checks_onprem_features_availability(AppEnv $appEnv)
    {
        $appEnv->isCloud()->willReturn(false);
        $appEnv->isDebug()->willReturn(false);
        $appEnv->isQa()->willReturn(false);

        $this->beConstructedWith($appEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(false);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_ONPREM])->shouldReturn(true);
    }
}
