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
use Symfony\Component\Validator\Constraints\Length;

class NewFeedbackType extends AbstractType
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
        $builder->add('title', 'text', array('label' => $this->phrase('portal.forms.label_title')));
        $builder->add('content', 'textarea', array('label' => 'portal.forms.label_content'));
        $builder->add('category', 'feedback_category', array(
            'person'      => $options['person'],
            'empty_value' => 'Select...',
        ));
        $builder->add('custom_data_collection', 'custom_feedback_fields');
        $builder->add('attachments', 'feedback_attachment_collection', array(
            'person' => $options['person'],
        ));
        $builder->add('more_attachments', 'submit', array(
            'validation_groups' => false,
            'label'             => $this->phrase('portal.forms.label_add_attachment'),
        ));

        if (!$options['person'] || $options['person'] instanceof PersonGuest) {
            $builder->add('name', 'text', array(
                'constraints'   => new Length(array('min' => 2)),
                'property_path' => 'person.name',
                'label'         => $this->phrase('portal.forms.label_name'),
            ));
            $builder->add('email', 'deskpro_person_email', array(
                'label'         => false,
                'property_path' => 'person.primary_email',
                'email_label'   => false,
            ));
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if ($this->captcha_decider->shouldRequireFeedbackCaptchaForCurrentPerson()) {
                $event->getForm()->add(
                    'captcha',
                    'deskpro_captcha',
                    array(
                        'mapped'         => false,
                        'error_bubbling' => false,
                    )
                );
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(
                array(
                    'person',
                )

            )
            ->setAllowedTypes(
                array(
                    'person' => 'Application\DeskPRO\Entity\Person',
                )
            )
            ->setDefaults(
                array(
                    'data_class'      => 'Application\DeskPRO\Entity\Feedback',
                    'agent_interface' => false,
                )
            )
        ;
    }

    private function phrase($name, array $vars = array())
    {
        return $this->language_manager->phrase($name, $vars);
    }

    public function getName()
    {
        return 'new_feedback';
    }
}
