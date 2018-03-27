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
     */
    public function beforeEnable(ContainerInterface $container);

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
     * The feature should be installed on install.
     *
     * @return bool
     */
    public function isEnabledOnInstall();

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
