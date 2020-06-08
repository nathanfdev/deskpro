<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface BetaFeatureInterface.
 */
interface BetaFeatureInterface extends FeatureInterface
{
    const BETA_FEATURES_KEY = 'beta_features';

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * This is arbitrary content that'll go in the features box on the main page.
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    public function getExtraInfoContent(ContainerInterface $container);

    /**
     * @return string
     */
    public function getShortDescription();

    /**
     * @return string
     */
    public function getEnableDescription();

    /**
     * @return string
     */
    public function getDisableDescription();

    /**
     * @param ContainerInterface $container
     * @param bool               $newInstall
     */
    public function beforeEnable(ContainerInterface $container, $newInstall = false);

    /**
     * @param ContainerInterface $container
     */
    public function beforeDisable(ContainerInterface $container);

    /**
     * Is feature enabled.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * When false, we should NOT show the [Disable] button in the features box.
     *
     * @return mixed
     */
    public function canBeDisabled();

    /**
     * The feature should be installed on install.
     *
     * @return bool
     */
    public function isEnabledOnInstall();

    /**
     * This represents when we are going to "force" the feature.
     *
     * @return \DateTime|false
     */
    public function getDueDate();

    /**
     * Feature release date (release from beta)
     * From this date feature enabled by default for new installs
     *
     * @return \DateTime|null
     */
    public function getDateReleased();

    /**
     * Require broadcast agent reload after enable/disable.
     *
     * @return bool
     */
    public function needAgentReload();

    /**
     * Feature route path, redirects after enable.
     *
     * @return string
     */
    public function getRoutePath();
}
