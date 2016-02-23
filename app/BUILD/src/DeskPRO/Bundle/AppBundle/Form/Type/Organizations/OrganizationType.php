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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Organizations;

use Application\DeskPRO\Entity\LabelOrganization;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\UsergroupsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class OrganizationType.
 */
class OrganizationType extends AbstractType
{
    /**
     * @var CustomFieldManager
     */
    private $field_manager;

    /**
     * PersonType constructor.
     *
     * @param CustomFieldManager $field_manager
     */
    public function __construct(CustomFieldManager $field_manager)
    {
        $this->field_manager = $field_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('parent', 'entity', [
                'class' => 'DeskPRO:Organization',
            ])
            ->add('picture_blob', 'auth_blob', [
                'property_path' => 'picture_blob',
            ])
            ->add('summary', 'text')
            ->add('importance', 'integer')
            ->add('labels', 'api_labels_collection', [
                'labels_class'   => LabelOrganization::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'organization',
            ])
            ->add('user_groups', UsergroupsType::class, [
                'is_agent_group' => false,
                'owner'          => $builder->getData(),
                'property_path'  => 'usergroups',
            ])
            ->add('email_domains', 'organization_domains', [
                'owner' => $builder->getData(),
            ])
            ->add('fields', 'deskpro_combined_type', [
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'organization';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => 'Application\DeskPRO\Entity\Organization',
            'agent_interface' => false,
        ]);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getCustomDataFields(array $options)
    {
        $field_defs  = $this->field_manager->getAvailableOrganizationDefs();
        $form_fields = [];

        foreach ($field_defs as $field_def) {
            $form_fields[] = [
                'name'    => $field_def->getId(),
                'type'    => 'deskpro_custom_data',
                'options' => [
                    'custom_def'      => $field_def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => $options['agent_interface'],
                    'label'           => $field_def->getTitle(),
                    'inline'          => true,
                ],
            ];
        }

        return $form_fields;
    }
}
