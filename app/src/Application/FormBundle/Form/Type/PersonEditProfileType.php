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


use Application\FormBundle\Form\FormFieldManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonEditProfileType extends AbstractType
{
    /**
     * @var FormFieldManager
     */
    private $field_manager;

    public function __construct(FormFieldManager $field_manager)
    {
        $this->field_manager = $field_manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name', 'text');
        $builder->add('last_name', 'text');

        $builder->add('timezone', 'timezone', array());

        $builder->add('language_id', 'deskpro_language', array(
            'view_context' => 'user'
        ));


        $field_manager = $this->field_manager;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($field_manager) {
            foreach ($field_manager->getAvailablePersonFields() as $field_def) {
                if (!$field_def->is_enabled) {
                    return false;
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
        return 'person_profile';
    }
}
