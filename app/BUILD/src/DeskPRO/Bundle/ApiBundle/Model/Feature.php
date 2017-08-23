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

namespace DeskPRO\Bundle\ApiBundle\Model;

use DeskPRO\Bundle\AppBundle\Features\BetaFeatureInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Feature.
 */
class Feature
{
    /**
     * Feature id.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $id;

    /**
     * Feature title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * Short description.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $shortDescription;

    /**
     * Explanation - what would happen when you are about to enable this feature.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $enableDescription;

    /**
     * Explanation - what would happen when you are about to disable this feature.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $disableDescription;

    /**
     * Redirect to the url.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $routePath;

    /**
     * Is this feature enabled?
     *
     * @JMS\Type("boolean")
     *
     * @var string
     */
    private $enabled;

    /**
     * Is this feature processing now?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $processing;

    /**
     * Constructor.
     *
     * @param BetaFeatureInterface $betaFeature
     * @param bool                 $processing
     */
    public function __construct(BetaFeatureInterface $betaFeature, $processing = false)
    {
        $this->id                 = $betaFeature->getId();
        $this->title              = $betaFeature->getTitle();
        $this->shortDescription   = $betaFeature->getShortDescription();
        $this->enableDescription  = $betaFeature->getEnableDescription();
        $this->disableDescription = $betaFeature->getDisableDescription();
        $this->routePath          = $betaFeature->getRoutePath();
        $this->enabled            = $betaFeature->isEnabled();
        $this->processing         = $processing;
    }
}
