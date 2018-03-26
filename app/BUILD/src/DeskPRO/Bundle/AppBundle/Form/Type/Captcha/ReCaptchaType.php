<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Captcha;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha\ValidRecaptcha2;
use ReCaptchaSecureToken\ReCaptchaToken;
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
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     * @param LanguageManager  $languageManager
     */
    public function __construct(SettingsResolver $settingsResolver, LanguageManager $languageManager)
    {
        $this->languageManager  = $languageManager;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $site_key = $this->getSiteKey();

        $view->vars['site_key'] = $site_key;

        $secure_token = null;
        if (self::isCloudRecapchaEnabled()) {
            $secure = new ReCaptchaToken([
                'site_key'    => $site_key,
                'site_secret' => $this->getCloudRecaptchaSecret(),
            ]);
            $session_id   = uniqid('recaptcha');
            $secure_token = $secure->secureToken($session_id);
        }

        $view->vars['secure_token'] = $secure_token;
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
        $resolver->setDefaults([
            'label'       => false,
            'mapped'      => false,
            'constraints' => [
                new ValidRecaptcha2(),
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
}
