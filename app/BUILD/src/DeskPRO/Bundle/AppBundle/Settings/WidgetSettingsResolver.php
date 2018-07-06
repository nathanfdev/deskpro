<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Request\UrlCorrectorFactory;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractTranslationModel;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\JwtSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings\WidgetBrandButtonTranslation;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatCustomField;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatPopupTranslation;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\WidgetOptions;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetUrlSettings;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Routing\PortalRouter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class WidgetSettingsResolver.
 */
class WidgetSettingsResolver extends AbstractBrandAwareSettingsResolver
{
    const CHAT_ENABLED          = 'core.apps_chat';
    const ENABLED_ON_PORTAL     = 'portal.widget.enabled';
    const ENABLED               = 'widget.enabled';
    const JWT_SECRET            = 'widget.jwt.secret';
    const JWT_REQUIRED          = 'widget.jwt.required';
    const CHAT_REQUIRE_LOGIN    = 'portal.chat.require_login';
    const CHAT_EMAIL_VALIDATION = 'portal.chat.email_validation';

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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var PortalPermissionsManager
     */
    private $permissionsManager;

    /**
     * @var PortalModeStorage
     */
    private $portalModeStorage;

    /**
     * @var UrlCorrectorFactory
     */
    private $urlCorrectorFactory;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param BrandAwareSettingsResolver $settingsResolver
     * @param EntityManager              $em
     * @param Packages                   $assetPackages
     * @param RouterInterface            $router
     * @param TokenStorageInterface      $tokenStorage
     * @param PortalPermissionsManager   $permissionsManager
     * @param PortalModeStorage          $portalModeStorage
     * @param UrlCorrectorFactory        $urlCorrectorFactory
     * @param LanguageManager            $languageManager
     */
    public function __construct(
        BrandAwareSettingsResolver $settingsResolver,
        EntityManager              $em,
        Packages                   $assetPackages,
        RouterInterface            $router,
        TokenStorageInterface      $tokenStorage,
        PortalPermissionsManager   $permissionsManager,
        PortalModeStorage          $portalModeStorage,
        UrlCorrectorFactory        $urlCorrectorFactory,
        LanguageManager            $languageManager
    ) {
        parent::__construct($settingsResolver);

        $this->em                  = $em;
        $this->assetPackages       = $assetPackages;
        $this->tokenStorage        = $tokenStorage;
        $this->permissionsManager  = $permissionsManager;
        $this->portalModeStorage   = $portalModeStorage;
        $this->urlCorrectorFactory = $urlCorrectorFactory;
        $this->languageManager     = $languageManager;

        if ($router instanceof PortalRouter) {
            $this->router = $router->getBaseRouter();
        } else {
            $this->router = $router;
        }
    }

    /**
     * @param Brand $brand
     *
     * @return bool
     */
    public function isChatEnabled(Brand $brand = null)
    {
        return (bool) $this->getSetting(self::CHAT_ENABLED, $brand);
    }

    /**
     * @param Brand $brand
     *
     * @return bool
     */
    public function isEnabledOnPortal(Brand $brand = null)
    {
        return (bool) $this->getSetting(self::ENABLED_ON_PORTAL, $brand);
    }

    /**
     * @param Brand $brand
     *
     * @return string|null
     */
    public function getJwtSecret(Brand $brand = null)
    {
        return $this->getSetting(self::JWT_SECRET, $brand);
    }

    /**
     * @param Brand $brand
     *
     * @return bool
     */
    public function isJwtRequired(Brand $brand = null)
    {
        return (bool) $this->getSetting(self::JWT_REQUIRED, $brand);
    }

    /**
     * @return bool
     */
    public function isChatRequireLogin()
    {
        return (bool) $this->getSetting(self::CHAT_REQUIRE_LOGIN);
    }

    /**
     * @return bool
     */
    public function isChatEmailValidation()
    {
        return (bool) $this->getSetting(self::CHAT_EMAIL_VALIDATION);
    }

    /**
     * @param Brand $brand
     *
     * @return WidgetSettings
     */
    public function getWidgetSettings(Brand $brand)
    {
        $model = new WidgetSettings();
        $model
            ->setUrl($this->getWidgetUrlSettings($brand))
            ->setSettings($this->getWidgetOptions($brand))
            ->setEnabledOnPortal($this->isEnabledOnPortal($brand))
            ->setBrand($brand)
            ->setJwtSettings($this->getJwtSettings($brand))
        ;

        return $model;
    }

    /**
     * @param Brand   $brand
     * @param Request $request
     *
     * @return WidgetUrlSettings
     */
    public function getWidgetUrlSettings(Brand $brand, Request $request = null)
    {
        $portalMode = $this->portalModeStorage->getMode();

        if ($portalMode && $portalMode->isBrand()) {
            $baseUrl     = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $helpdeskUrl = rtrim($baseUrl, '/').'/brand-'.$brand->getId();
        } else {
            $baseUrl     = $this->router->generate('portal_home', ['brand' => $brand], UrlGeneratorInterface::ABSOLUTE_URL);
            $helpdeskUrl = $baseUrl;
        }

        if ($request) {
            $urlCorrector = $this->urlCorrectorFactory->createUrlCorrector($brand);

            $baseUrl     = $urlCorrector->correctUrlScheme($baseUrl, $request);
            $helpdeskUrl = $urlCorrector->correctUrlScheme($helpdeskUrl, $request);
        }

        $loaderUrl = $this->assetPackages->getUrl('widget_loader.min.js', 'app_assets');
        $widgetUrl = $this->assetPackages->getUrl('DeskPRO_WidgetBundle.js', 'app_assets');
        $basePath  = $request ? $request->getBasePath() : '';

        if (!preg_match('#^https?://#i', $loaderUrl)) {
            $loaderUrl = rtrim(str_replace($basePath, '', $baseUrl), '/').$loaderUrl;
        }
        if (!preg_match('#^https?://#i', $widgetUrl)) {
            $widgetUrl = rtrim(str_replace($basePath, '', $baseUrl), '/').$widgetUrl;
        }

        $model = new WidgetUrlSettings();
        $model
            ->setWidgetLoader($loaderUrl)
            ->setWidgetBundle($widgetUrl)
            ->setHelpdesk($helpdeskUrl)
        ;

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return WidgetOptions
     */
    public function getWidgetOptions(Brand $brand)
    {
        $model = new WidgetOptions();
        $model
            ->setGlobal($this->getWidgetGlobalOptions($brand))
            ->setBrand($this->getWidgetBrandOptions($brand))
        ;

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return WidgetGlobalSettings
     */
    public function getWidgetGlobalOptions(Brand $brand = null)
    {
        $model = new WidgetGlobalSettings();
        $chat  = $model->getChat();
        $chat->setEnabled($this->isChatEnabled($brand));

        $company = $model->getCompany();
        $company->setName($this->getSetting('core.site_name'));

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return WidgetBrandSettings
     */
    public function getWidgetBrandOptions(Brand $brand)
    {
        $model     = null;
        $dataStore = $this->em->getRepository(DataStore::class)->findOneBy([
            'name' => 'widget.brand_settings.'.$brand->getId(),
        ]);

        if ($dataStore) {
            $model = $dataStore->getData('brand_settings');
        }

        // create new brand settings
        if (!$model instanceof WidgetBrandSettings) {
            $model = new WidgetBrandSettings();
        }

        // filter translations
        $languages    = $this->em->getRepository(Language::class)->findAll();
        $languagesIds = array_map(function (Language $language) {
            return $language->getId();
        }, $languages);

        $buttonSettings = $model->getButton();
        foreach ($languages as $language) {
            $translation = $buttonSettings->getTranslation($language->getId());
            if (!$translation) {
                $translation = new WidgetBrandButtonTranslation();
                $translation->setLanguage($language->getId());

                $buttonSettings->getTranslations()->add($translation);
            }

            // chat is available button label
            if (!$translation->getName()) {
                $translation->setName($this->languageManager->phrase('portal.widget.help_button', [], $language));
            }

            // chat is unavailable button label (ticket fallback)
            if (!$translation->getContactUs()) {
                $translation->setContactUs($this->languageManager->phrase('portal.widget.help_ticket_button', [], $language));
            }
        }

        $popupSettings = $model->getChat()->getPopup();
        foreach ($languages as $language) {
            $translation = $popupSettings->getTranslation($language->getId());
            if (!$translation) {
                $translation = new WidgetBrandChatPopupTranslation();
                $translation->setLanguage($language->getId());

                $popupSettings->getTranslations()->add($translation);
            }
            if (!$translation->getTitle()) {
                $translation->setTitle($this->languageManager->phrase('portal.widget.popup_title', [], $language));
            }
            if (!$translation->getMessage()) {
                $translation->setMessage($this->languageManager->phrase('portal.widget.popup_message', [], $language));
            }
            if (!$translation->getHeading()) {
                $translation->setHeading($this->languageManager->phrase('portal.widget.popup_heading', [], $language));
            }
            if (!$translation->getSubheading()) {
                $translation->setSubheading($this->languageManager->phrase('portal.widget.popup_subheading', [], $language));
            }
            if (!$translation->getStartButton()) {
                $translation->setStartButton($this->languageManager->phrase('portal.widget.popup_start_button', [], $language));
            }
        }

        foreach ([$buttonSettings->getTranslations(), $popupSettings->getTranslations()] as $propTranslations) {
            /** @var AbstractTranslationModel[]|ArrayCollection $propTranslations */
            foreach ($propTranslations as $translation) {
                if (!in_array($translation->getLanguage(), $languagesIds)) {
                    $propTranslations->removeElement($translation);
                }
            }
        }

        $buttonSettings->setTranslations(new ArrayCollection($buttonSettings->getTranslations()->getValues()));
        $popupSettings->setTranslations(new ArrayCollection($popupSettings->getTranslations()->getValues()));

        // custom fields
        $chatSettings = $model->getChat();
        $chatFields   = $this->em->getRepository(CustomDefChat::class)->findAll();
        $chatFieldIds = [];

        // add missing chat fields
        if (!$chatSettings->getCustomFields() instanceof ArrayCollection) {
            $chatSettings->setCustomFields(new ArrayCollection());
        }
        foreach ($chatFields as $chatField) {
            $chatFieldIds[] = $chatField->getId();

            if (!$chatSettings->getCustomField($chatField->getId())) {
                $brandCustomField = new WidgetBrandChatCustomField();
                $brandCustomField->setId($chatField->getId());
                $brandCustomField->setIsEnabled($chatField->isEnabled());
                $brandCustomField->setDisplayOrder($chatField->getDisplayOrder());

                $chatSettings->addCustomField($brandCustomField);
            }
        }

        // remove deleted fields
        foreach ($chatSettings->getCustomFields() as $brandCustomField) {
            if (!in_array($brandCustomField->getId(), $chatFieldIds)) {
                $chatSettings->removeCustomField($brandCustomField);
            }
        }

        // usergroups
        $userGroups   = $this->em->getRepository(Usergroup::class)->findAll();
        $userGroupIds = new ArrayCollection();
        if (!$chatSettings->getUserGroups() instanceof ArrayCollection) {
            foreach ($userGroups as $userGroup) {
                $userGroupIds->add($userGroup->getId());
            }
        } else {
            $existUserGroupsIds = [];
            foreach ($userGroups as $userGroup) {
                $existUserGroupsIds[$userGroup->getId()] = $userGroup->getId();
            }

            foreach ($chatSettings->getUserGroups() as $userGroupId) {
                if (isset($existUserGroupsIds[$userGroupId])) {
                    $userGroupIds->add($userGroupId);
                }
            }
        }

        $chatSettings->setUserGroups($userGroupIds);

        return $model;
    }

    /**
     * @param Brand $brand
     *
     * @return JwtSettings
     */
    public function getJwtSettings(Brand $brand)
    {
        $model = new JwtSettings();
        $model->setSecret($this->getJwtSecret($brand));
        $model->setRequired($this->isJwtRequired($brand));

        return $model;
    }
}
