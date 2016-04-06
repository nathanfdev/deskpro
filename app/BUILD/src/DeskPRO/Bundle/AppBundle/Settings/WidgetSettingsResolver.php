<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\DataStore;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\WidgetOptions;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetUrlSettings;
use DeskPRO\Bundle\PortalBundle\Routing\PortalRouter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class WidgetSettingsResolver.
 */
class WidgetSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    const REQUIRE_LOGIN     = 'portal.chat.require_login';
    const EMAIL_VALIDATION  = 'portal.chat.email_validation';
    const ENABLED_ON_PORTAL = 'portal.widget.enabled';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Packages
     */
    private $assetPackages;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param EntityManager              $em
     * @param Packages                   $assetPackages
     * @param RouterInterface            $router
     */
    public function __construct(
        BrandAwareSettingsResolver $settingsResolver,
        EntityManager              $em,
        Packages                   $assetPackages,
        RouterInterface            $router
    ) {
        parent::__construct($settingsResolver);

        if ($router instanceof PortalRouter) {
            $router = $router->getBaseRouter();
        }

        $this->em            = $em;
        $this->assetPackages = $assetPackages;
        $this->router        = $router;
    }

    /**
     * @return bool
     */
    public function isPortalEmailValidation()
    {
        return (bool) $this->getSetting(self::EMAIL_VALIDATION);
    }

    /**
     * @return bool
     */
    public function isPortalRequireLogin()
    {
        return (bool) $this->getSetting(self::REQUIRE_LOGIN);
    }

    /**
     * @return bool
     */
    public function isEnabledOnPortal()
    {
        return (bool) $this->getSetting(self::ENABLED_ON_PORTAL);
    }

    /**
     * @return WidgetSettings
     */
    public function getWidgetSettings()
    {
        $model = new WidgetSettings();
        $model
            ->setUrl($this->getWidgetUrlSettings())
            ->setSettings($this->getWidgetOptions())
            ->setEnabledOnPortal($this->isEnabledOnPortal())
        ;

        return $model;
    }

    /**
     * @return WidgetUrlSettings
     */
    public function getWidgetUrlSettings()
    {
        $dpUrl     = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $loaderUrl = $this->assetPackages->getUrl('widget_loader.js', 'app_assets');
        $widgetUrl = $this->assetPackages->getUrl('DeskPRO_WidgetBundle.js', 'app_assets');

        if (!preg_match('#^https?://#i', $loaderUrl)) {
            $loaderUrl = rtrim($dpUrl, '/').$loaderUrl;
        }
        if (!preg_match('#^https?://#i', $widgetUrl)) {
            $widgetUrl = rtrim($dpUrl, '/').$widgetUrl;
        }

        $model = new WidgetUrlSettings();
        $model
            ->setWidgetLoader($loaderUrl)
            ->setWidgetBundle($widgetUrl)
            ->setHelpdesk($dpUrl)
        ;

        return $model;
    }

    /**
     * @return WidgetOptions
     */
    public function getWidgetOptions()
    {
        $model = new WidgetOptions();
        $model
            ->setGlobal($this->getWidgetGlobalOptions())
            ->setBrand($this->getWidgetBrandOptions())
        ;

        return $model;
    }

    /**
     * @return WidgetGlobalSettings
     */
    public function getWidgetGlobalOptions()
    {
        $model = new WidgetGlobalSettings();

        $chat = $model->getChat();
        $chat
            ->setEmailValidation($this->isPortalEmailValidation())
            ->setRequireLogin($this->isPortalRequireLogin())
        ;

        $company = $model->getCompany();
        $company->setName($this->getSetting('core.site_name'));

        return $model;
    }

    /**
     * @return WidgetBrandSettings
     */
    public function getWidgetBrandOptions()
    {
        $model     = null;
        $dataStore = $this->em->getRepository(DataStore::class)->findOneBy(['name' => 'widget.brand_settings']);
        if ($dataStore) {
            $model = $dataStore->getData('brand_settings');
        }

        if (!$model instanceof WidgetBrandSettings) {
            $model = new WidgetBrandSettings();
        }

        return $model;
    }
}
