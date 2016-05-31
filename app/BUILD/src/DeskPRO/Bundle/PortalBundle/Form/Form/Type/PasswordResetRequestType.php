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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PasswordResetRequestType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captcha_decider;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $language_manager;

    /**
     * PasswordResetRequestType constructor.
     *
     * @param CaptchaDecider  $captcha_decider
     * @param LanguageManager $language_manager
     */
    public function __construct(CaptchaDecider $captcha_decider, LanguageManager $language_manager)
    {
        $this->captcha_decider  = $captcha_decider;
        $this->language_manager = $language_manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', [
            'label' => $this->phrase('portal.forms.label_email'),
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();

                if ($this->captcha_decider->shouldRequireForgotPasswordCaptchaForCurrentPerson()) {
                    $form->add('captcha', 'deskpro_captcha');
                }
            }
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'request_password_reset';
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    private function phrase($name, array $vars = [])
    {
        return $this->language_manager->phrase($name, $vars);
    }
}
