<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Captcha;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha\HcValidRecaptcha2;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha\ValidRecaptcha2;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReCaptchaType.
 */
class ReCaptchaType extends AbstractType
{
    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var PortalBrandThemeLoader
     */
    private $portalBrandThemeLoader;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     * @param BrandStack $brandStack
     * @param PortalBrandThemeLoader|null $portalBrandThemeLoader
     */
    public function __construct(
        SettingsResolver $settingsResolver,
        BrandStack $brandStack,
        PortalBrandThemeLoader $portalBrandThemeLoader = null
    ) {
        $this->settingsResolver       = $settingsResolver;
        $this->brandStack             = $brandStack;
        $this->portalBrandThemeLoader = $portalBrandThemeLoader;
    }

    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['site_key'] = $this->getSiteKey();

        $view->vars['recaptcha_version'] = $this->getRecaptchaVersion();

        $secureToken = null;
        if (self::isCloudRecapchaEnabled()) {
            // TODO cloud recaptcha
        }

        $view->vars['secure_token'] = $secureToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'deskpro_recaptcha';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $contraint = $this->isHelpcenter() ? new HcValidRecaptcha2() : new ValidRecaptcha2();
        $resolver->setDefaults([
            'label'       => false,
            'mapped'      => false,
            'constraints' => [
                $contraint,
            ],
        ]);
    }

    /**
     * @return mixed
     */
    protected function getSecretKey()
    {
        $setting_secret = $this->settingsResolver->getGlobalSettings()->get('core.recaptcha2_secret_key');

        if (strlen($setting_secret) > 0) {
            return $setting_secret;
        }

        return self::getCloudRecaptchaSecret();
    }

    /**
     * @return null|string
     */
    protected function getSiteKey()
    {
        $setting_key = $this->settingsResolver->getGlobalSettings()->get('core.recaptcha2_site_key');

        if (strlen($setting_key) > 0) {
            return $setting_key;
        }

        // if there is no setting key, try the cloud constants
        return self::getCloudRecaptchaSiteKey();
    }

    /**
     * @return mixed
     */
    protected function getRecaptchaVersion()
    {
        $version = $this->settingsResolver->getGlobalSettings()->get('core.recaptcha_version');

        if (is_numeric($version)) {
            return $version;
        }

        return self::getCloudRecaptchaVersion();
    }

    /**
     * Returns true/false on if the cloud recaptcha is enabled. This
     * condition is NOT sufficient to use the cloud credentials, because
     * a cloud account can still install their own recaptcha app (with
     * their google credentials).
     *
     * Must first check to see if the setting "core.use_recaptcha2" is enabled
     * and use the credentials from settings.
     *
     * @return bool
     */
    public static function isCloudRecapchaEnabled()
    {
        return
            (defined('DPC_IS_CLOUD') && DPC_IS_CLOUD)
            &&
            (defined('DP_RECAPTCHA2_CLOUD') && DP_RECAPTCHA2_CLOUD)
        ;
    }

    /**
     * @return null|void
     */
    public static function getCloudRecaptchaSiteKey()
    {
        if (self::isCloudRecapchaEnabled()) {
            return defined('DP_RECAPTCHA2_SITE_KEY') ? DP_RECAPTCHA2_SITE_KEY : null;
        }

        return;
    }

    /**
     * @return null|void
     */
    public static function getCloudRecaptchaSecret()
    {
        if (self::isCloudRecapchaEnabled()) {
            return defined('DP_RECAPTCHA2_SECRET_KEY') ? DP_RECAPTCHA2_SECRET_KEY : null;
        }

        return;
    }

    /**
     * @return null|void
     */
    public static function getCloudRecaptchaVersion()
    {
        if (self::isCloudRecapchaEnabled()) {
            return defined('DP_RECAPTCHA_VERSION') ? DP_RECAPTCHA_VERSION : null;
        }

        return;
    }

    private function isHelpcenter()
    {
        if ($this->portalBrandThemeLoader) {
            return $this->portalBrandThemeLoader->getPortalBrandTheme($this->brandStack->getActive()->getBrand())->getActiveThemeSet()->getThemeId() === 'helpcenter';
        }

        return false;
    }
}
