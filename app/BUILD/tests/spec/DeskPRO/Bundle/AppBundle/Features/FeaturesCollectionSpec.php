<?php

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
