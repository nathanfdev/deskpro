<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Captcha;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CaptchaType.
 */
class DpCaptchaType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var array
     */
    private $captchaConfig;

    /**
     * Constructor.
     *
     * @param BrandStack      $brand_stack
     * @param LanguageManager $language_manager
     * @param array           $captchaConfig
     */
    public function __construct(BrandStack $brand_stack, LanguageManager $language_manager, array $captchaConfig)
    {
        $this->language_manager = $language_manager;
        $this->brand_stack      = $brand_stack;
        $this->captchaConfig    = $captchaConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $config = $form->getRoot()->getConfig();

            // if saved_form_subrequest option is true on the root form, ignore all captcha (don't add it!)
            if ($config->hasOption('saved_form_subrequest') && $config->getOption('saved_form_subrequest', false)) {
                $form->getRoot()->remove($form->getName());

                return;
            }

            if ($this->isRecaptchaEnabled()) {
                $form->add('captcha', ReCaptchaType::class);
            } else {
                $form->add('captcha', CaptchaType::class, [
                    'label'           => false,
                    'as_url'          => true,
                    'invalid_message' => 'portal.forms.error_captcha',
                    // Workaround to avoid null bypass_code to be cast as a string that de-require the captcha
                    'bypass_code' => rand(0, 123456),
                    'length'      => $this->captchaConfig['length'],
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'deskpro_captcha';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label'  => false,
            'mapped' => false,
            'help'   => function (Options $options) {
                if ($this->isRecaptchaEnabled()) {
                    return false;
                }

                return $this->language_manager->phrase('portal.forms.label_captcha');
            },
            'allow_extra_fields' => function (Options $options) {
                // if its a saved form subrequest, allow extra fields
                // this is because we disable things like catpcha, and csrf, and they may
                // be present in the form data even though we've removed them from the actual form
                return isset($options['saved_form_subrequest']) && $options['saved_form_subrequest'];
            },
        ]);
    }

    /**
     * @return mixed
     */
    protected function isRecaptchaEnabled()
    {
        return $this->brand_stack->getActive()->getSetting('core.use_recaptcha2')
            || ReCaptchaType::isCloudRecapchaEnabled();
    }
}
