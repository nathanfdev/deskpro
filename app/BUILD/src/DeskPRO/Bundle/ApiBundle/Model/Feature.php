<?php

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
