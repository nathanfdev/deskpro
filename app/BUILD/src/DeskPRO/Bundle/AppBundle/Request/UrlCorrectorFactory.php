<?php

namespace DeskPRO\Bundle\AppBundle\Request;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;

/**
 * Class UrlCorrectorFactory.
 */
class UrlCorrectorFactory
{
    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param AppEnvInterface            $appEnv
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(AppEnvInterface $appEnv, BrandAwareSettingsResolver $settingsResolver)
    {
        $this->appEnv           = $appEnv;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param Brand $brand
     *
     * @return UrlCorrector
     */
    public function createUrlCorrector(Brand $brand)
    {
        $options = [
            'autoCorrectScheme' => $this->settingsResolver->getSetting('core.deskpro_url_autocorrect', $brand),
            'autoCorrectHost'   => $this->appEnv->isCloud() ? $this->settingsResolver->getSetting('core.deskpro_url_autocorrect', $brand) : false,
            'helpdeskUrl'       => $this->settingsResolver->getSetting('core.deskpro_url', $brand),
        ];

        return new UrlCorrector($options);
    }
}
