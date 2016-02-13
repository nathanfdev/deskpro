<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CaptchaType.
 */
class CaptchaType extends AbstractType
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
     * Constructor.
     *
     * @param BrandStack      $brand_stack
     * @param LanguageManager $language_manager
     */
    public function __construct(BrandStack $brand_stack, LanguageManager $language_manager)
    {
        $this->language_manager = $language_manager;
        $this->brand_stack      = $brand_stack;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            // if saved_form_subrequest option is true on the root form, ignore all captcha (don't add it!)
            if ($form->getRoot()->getConfig()->getOption('saved_form_subrequest', false)) {
                $form->getRoot()->remove($form->getName());

                return;
            }

            if ($this->isRecaptchaEnabled()) {
                $form->add('captcha', 'deskpro_recaptcha');
            } else {
                $form->add('captcha', 'captcha', [
                    'label'           => false,
                    'as_url'          => true,
                    'invalid_message' => 'portal.forms.error_captcha',
                ]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_captcha';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
                return $options['saved_form_subrequest'];
            },
        ]);
    }

    /**
     * @return mixed
     */
    protected function isRecaptchaEnabled()
    {
        return $this->brand_stack->getActive()->getSetting('core.use_recaptcha2') || ReCaptchaType::isCloudRecapchaEnabled();
    }
}
