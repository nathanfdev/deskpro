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
use DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class NewFeedbackType.
 */
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

    /**
     * @var FormFieldManager
     */
    private $field_manager;

    /**
     * Constructor.
     *
     * @param CaptchaDecider   $captcha_decider
     * @param LanguageManager  $language_manager
     * @param FormFieldManager $field_manager
     */
    public function __construct(CaptchaDecider $captcha_decider, LanguageManager $language_manager, FormFieldManager $field_manager)
    {
        $this->captcha_decider  = $captcha_decider;
        $this->language_manager = $language_manager;
        $this->field_manager    = $field_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label'       => $this->phrase('portal.forms.label_title'),
                'constraints' => [
                    new NotBlank(['message' => 'portal.forms.error_required']),
                ],
            ])
            ->add('content', 'textarea', [
                'label'       => 'portal.forms.label_content',
                'constraints' => [
                    new NotBlank(['message' => 'portal.forms.error_required']),
                ],
            ])
            ->add('category', 'feedback_category', [
                'person'      => $options['person'],
                'empty_value' => $this->phrase('portal.forms.label_select'),
                'constraints' => [
                    new NotNull(['message' => 'portal.forms.error_required']),
                ],
            ])
            ->add('custom_data', 'deskpro_combined_type', [
                'forms' => $this->getCustomDataForms($options),
            ])
            ->add('attachments', 'feedback_attachment_collection', [
                'person' => $options['person'],
            ])
            ->add('more_attachments', 'submit', [
                'validation_groups' => false,
                'label'             => $this->phrase('portal.forms.label_add_attachment'),
            ])
        ;

        if (!$options['person'] || $options['person'] instanceof PersonGuest) {
            $builder
                ->add('name', 'text', [
                    'constraints'   => new Length(['minMessage' => 'portal.forms.error_length_min', 'min' => 2]),
                    'property_path' => 'person.name',
                    'label'         => $this->phrase('portal.forms.label_name'),
                ])
                ->add('email', 'deskpro_person_email', [
                    'label'         => false,
                    'property_path' => 'person.primary_email',
                    'constraints'   => [], // ignore the "unqiue entity" constraint here
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if ($this->captcha_decider->shouldRequireFeedbackCaptchaForCurrentPerson()) {
                $event->getForm()->add(
                    'captcha',
                    'deskpro_captcha',
                    [
                        'mapped'         => false,
                        'error_bubbling' => false,
                    ]
                );
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired([
                'person',
            ])
            ->setAllowedTypes([
                'person' => 'Application\DeskPRO\Entity\Person',
            ])
            ->setDefaults([
                'data_class'      => 'Application\DeskPRO\Entity\Feedback',
                'agent_interface' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'new_feedback';
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getCustomDataForms(array $options)
    {
        $forms      = [];
        $field_defs = $this->field_manager->getFeedbackFields();

        foreach ($field_defs as $field_def) {
            $forms[] = [
                'name'    => 'custom_feedback_def_'.$field_def->getId(),
                'type'    => 'deskpro_custom_data',
                'options' => [
                    'custom_data_field' => $field_def,
                    'property_path'     => 'custom_data',
                    'agent_interface'   => $options['agent_interface'],
                    'label'             => $field_def->title,
                ],
            ];
        }

        return $forms;
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    protected function phrase($name, array $vars = [])
    {
        return $this->language_manager->phrase($name, $vars);
    }
}
