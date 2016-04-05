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
use Doctrine\ORM\EntityManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

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
    private $assets;

    /**
     * @var Router
     */
    private $router;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param EntityManager              $em
     * @param Packages                   $assets
     * @param Router                     $router
     */
    public function __construct(BrandAwareSettingsResolver $settingsResolver, EntityManager $em, Packages $assets, Router $router)
    {
        parent::__construct($settingsResolver);

        $this->em     = $em;
        $this->assets = $assets;
        $this->router = $router;
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
            ->setEnabledOnPortal($this->getSetting(self::ENABLED_ON_PORTAL))
        ;

        return $model;
    }

    /**
     * @return WidgetUrlSettings
     */
    public function getWidgetUrlSettings()
    {
        $model = new WidgetUrlSettings();
        $model
            ->setWidgetLoader($this->assets->getUrl('widget_loader.js', 'app_assets'))
            ->setWidgetBundle($this->assets->getUrl('DeskPRO_WidgetBundle.js', 'app_assets'))
            ->setHelpdesk($this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL))
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

    /**
     * @return array
     *
     * @deprecated BC
     */
    public function getDefaultBrandSettings()
    {
        return [
            'widget' => [
                'type'                  => 'column',
                'position'              => 'right',
                'agent_polling_timeout' => 10,
            ],
            'button' => [
                'size'   => 'medium',
                'name'   => 'Help',
                'colors' => [
                    'background' => '#62ad8c',
                    'text'       => '#ffffff',
                    'border'     => '#4e9576',
                ],
            ],
            'chat' => [
                'enabled'           => true,
                'request_user_info' => true,
                'proactive'         => true,
                'popup'             => [
                    'title'      => 'DeskPRO Customer Support',
                    'message'    => 'Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.',
                    'reply_type' => 'buttons',
                ],
                'begin_mode'      => 'form',
                'waiting_timeout' => 30,
            ],
        ];
    }

    /**
     * @return array
     *
     * @deprecated BC
     */
    public function getPortalBrandSettings()
    {
        $brand_settings = $this->getDefaultBrandSettings();
        $data_store     = $this->em->getRepository(DataStore::class)->findOneBy([
            'name' => 'widget.portal_brand_settings',
        ]);

        if ($data_store) {
            $brand_settings = array_merge($brand_settings, $data_store->getData('brand_settings') ?: []);
        }

        return $brand_settings;
    }

    /**
     * @return array
     *
     * @deprecated BC
     */
    public function getCompanySettings()
    {
        return [
            'name' => $this->getSetting('core.site_name'),
            'logo' => '',
        ];
    }
}
