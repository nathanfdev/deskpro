<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;


use Application\DeskPRO\Entity\CustomDataPerson;
use Application\FormBundle\Form\FormFieldManager;
use Application\LanguageBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonRegistrationType extends AbstractType
{
    /**
     * @var FormFieldManager
     */
    private $field_manager;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    public function __construct(FormFieldManager $field_manager, LanguageManager $language_manager)
    {
        $this->field_manager = $field_manager;
        $this->language_manager = $language_manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'required' => true,
            'constraints' => array(
                new NotBlank()
            ),
        ));

        $builder->add('primary_email', 'deskpro_person_email', array(
            'required' => true,
            'label' => false
        ));

        $builder->add('password', 'repeated', array(
            'first_name' => 'password',
            'first_options' => array('label' => 'Password'),
            'second_name' => 'confirm',
            'second_options' => array('label' => 'Confirm'),
            'type' => 'password',
            'mapped' => false,
            'required' => true,
            'constraints' => array(
                new NotBlank()
            )
        ));

        $builder->add('timezone', 'timezone', array());

        if ($this->language_manager->isMultiLanguagePortal()) {
            $builder->add('language_id', 'deskpro_language', array(
                'view_context' => 'user'
            ));
        }


        $field_manager = $this->field_manager;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($field_manager) {
            foreach ($field_manager->getAvailablePersonFields() as $field_def) {
                if (!$field_def->is_enabled) {
                    continue;
                }

                $id = $field_def->getId();
                $event->getForm()->add(
                    $id,
                    'deskpro_custom_data_person',
                    array(
                        'custom_data_field' => $field_def,
                        'person' => $event->getData(),
                        'property_path' => sprintf('getCustomDataCollection[%s]', $id),
                        'agent_interface' => false,
                        'label' => false
                    )
                );
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) use ($field_manager) {
            $event->getData()->setPassword($event->getForm()->get('password')->getData());
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Application\DeskPRO\Entity\Person'
            )
        );

        $resolver->setRequired(
            array('settings')
        );

        $resolver->setAllowedTypes(
            array(
                'settings' => 'Application\DeskPRO\NewSettings\SettingsBag'
            )
        );
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'person_registration';
    }
}
