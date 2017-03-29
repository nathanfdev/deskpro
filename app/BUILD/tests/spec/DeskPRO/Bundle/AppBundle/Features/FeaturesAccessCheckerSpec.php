<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\AppBundle\Features;

use DeskPRO\Bundle\AppBundle\Features\FeatureInterface;
use DpRun\DpEnv;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Features\FeaturesAccessChecker
 */
class FeaturesAccessCheckerSpec extends ObjectBehavior
{
    public function it_checks_features_availability_in_dev_mode(DpEnv $dpEnv)
    {
        $dpEnv->isDebug()->willReturn(true);

        $this->beConstructedWith($dpEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_EVERYWHERE])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_ONPREM])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_QA])->shouldReturn(true);
    }

    public function it_checks_everywhere_features_availability(DpEnv $dpEnv)
    {
        $dpEnv->isCloud()->willReturn(false);
        $dpEnv->isDebug()->willReturn(false);
        $dpEnv->isQa()->willReturn(false);

        $this->beConstructedWith($dpEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_EVERYWHERE])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_QA])->shouldReturn(false);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(false);
    }

    public function it_checks_qa_features_availability(DpEnv $dpEnv)
    {
        $dpEnv->isCloud()->willReturn(false);
        $dpEnv->isDebug()->willReturn(false);
        $dpEnv->isQa()->willReturn(true);

        $this->beConstructedWith($dpEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_QA])->shouldReturn(true);
    }

    public function it_checks_cloud_features_availability(DpEnv $dpEnv)
    {
        $dpEnv->isCloud()->willReturn(true);
        $dpEnv->isDebug()->willReturn(false);
        $dpEnv->isQa()->willReturn(false);

        $this->beConstructedWith($dpEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(true);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_ONPREM])->shouldReturn(false);
    }

    public function it_checks_onprem_features_availability(DpEnv $dpEnv)
    {
        $dpEnv->isCloud()->willReturn(false);
        $dpEnv->isDebug()->willReturn(false);
        $dpEnv->isQa()->willReturn(false);

        $this->beConstructedWith($dpEnv);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->shouldReturn(false);
        $this->isAvailable([FeatureInterface::AVAILABLE_AT_ONPREM])->shouldReturn(true);
    }
}
