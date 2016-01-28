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

use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captcha_decider;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    public function __construct(CaptchaDecider $captcha_decider, LanguageManager $language_manager)
    {
        $this->captcha_decider  = $captcha_decider;
        $this->language_manager = $language_manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content_real', 'textarea', array(
            'label'       => $this->language_manager->phrase('portal.forms.label_comment'),
            'constraints' => array(
                new NotBlank(['message' => 'portal.forms.error_required']),
            ),
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $comment = $event->getData();
            $form = $event->getForm();

            // if this is a guest, ask for more information
            if ($comment->getPerson() instanceof PersonGuest) {
                $form->add('name', 'text', array(
                    'label'       => $this->language_manager->phrase('portal.forms.label_full_name'),
                    'constraints' => array(
                        new NotBlank(['message' => 'portal.forms.error_required']),
                    ),
                ));
                $form->add('email', 'email', array(
                    'label'       => $this->language_manager->phrase('portal.forms.label_email'),
                    'constraints' => array(
                        new NotBlank(['message' => 'portal.forms.error_required']),
                        new Email(['message' => 'portal.forms.error_email_invalid']),
                    ),
                ));
            }

            if ($this->captcha_decider->shouldRequireCommentCaptchaForCurrentPerson()) {
                $form->add('captcha', 'deskpro_captcha');
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\\DeskPRO\\Entity\\CommentAbstract',
        ));

        $resolver->setRequired(array(
            'person',
        ));

        $resolver->setAllowedTypes(array(
            'person' => 'Application\\DeskPRO\\Entity\\Person',
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'comment';
    }
}
