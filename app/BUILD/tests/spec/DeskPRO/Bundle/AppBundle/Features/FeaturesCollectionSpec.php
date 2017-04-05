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

use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use DeskPRO\Bundle\AppBundle\Features\FeatureInterface;
use DeskPRO\Bundle\AppBundle\Features\FeaturesAccessChecker;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Features\FeaturesCollection
 */
class FeaturesCollectionSpec extends ObjectBehavior
{
    public function let(
        BetaFeatureInterface $feature,
        BetaFeatureInterface $allFeature,
        BetaFeatureInterface $qaFeature,
        BetaFeatureInterface $cloudFeature,
        BetaFeatureInterface $onPremFeature
    ) {
        $feature->getId()->willReturn('test.feature');

        $allFeature->getId()->willReturn('all.feature');
        $allFeature->getAvailability()->willReturn([FeatureInterface::AVAILABLE_EVERYWHERE]);

        $qaFeature->getId()->willReturn('qa.feature');
        $qaFeature->getAvailability()->willReturn([FeatureInterface::AVAILABLE_AT_QA]);

        $cloudFeature->getId()->willReturn('cloud.feature');
        $cloudFeature->getAvailability()->willReturn([FeatureInterface::AVAILABLE_AT_CLOUD]);

        $onPremFeature->getId()->willReturn('onprem.feature');
        $onPremFeature->getAvailability()->willReturn([FeatureInterface::AVAILABLE_AT_ONPREM]);
    }

    public function it_adds_features_and_skip_dupes(
        FeaturesAccessChecker $accessChecker,
        BetaFeatureInterface $feature
    ) {
        $this->beConstructedWith($accessChecker);
        $this->addFeature($feature);
        $this->addFeature($feature);
        $this->count()->shouldBeEqualTo(1);
    }

    public function it_returns_feature_by_id(
        FeaturesAccessChecker $accessChecker,
        BetaFeatureInterface $feature
    ) {
        $this->beConstructedWith($accessChecker);
        $this->addFeature($feature);
        $gotFeature = $this->getFeature('test.feature');
        $gotFeature->getId()->shouldBeEqualTo('test.feature');
    }

    public function it_returns_null_if_feature_is_not_found(
        FeaturesAccessChecker $accessChecker
    ) {
        $this->beConstructedWith($accessChecker);
        $gotFeature = $this->getFeature('unknown.feature');
        $gotFeature->shouldBeEqualTo(null);
    }

    public function it_filters_features_availability(
        FeaturesAccessChecker $accessChecker,
        BetaFeatureInterface $qaFeature,
        BetaFeatureInterface $cloudFeature
    ) {
        $accessChecker->isAvailable([FeatureInterface::AVAILABLE_AT_QA])->willReturn(true);
        $accessChecker->isAvailable([FeatureInterface::AVAILABLE_AT_CLOUD])->willReturn(false);

        $this->beConstructedWith($accessChecker);
        $this->addFeature($qaFeature);
        $this->addFeature($cloudFeature);
        $this->getAvailableFeatures()->shouldContain($qaFeature);
        $this->getAvailableFeatures()->shouldNotContain($cloudFeature);
    }
}
