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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Form\Validator\Constraints\ValidRecaptcha2;
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
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param BrandStack      $brandStack
     * @param LanguageManager $languageManager
     */
    public function __construct(BrandStack $brandStack, LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
        $this->brandStack      = $brandStack;
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
        $setting_secret = $this->brandStack->getActive()->getSetting('core.recaptcha2_secret_key');

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
        $setting_key = $this->brandStack->getActive()->getSetting('core.recaptcha2_site_key');

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
