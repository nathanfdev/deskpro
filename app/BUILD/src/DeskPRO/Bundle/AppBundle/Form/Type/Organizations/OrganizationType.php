<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Organizations;

use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\LabelOrganization;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\ContactData\ContactDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\UsergroupsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('parent', 'entity', [
                'class'    => Organization::class,
                'required' => false,
            ])
            ->add('picture_blob', BlobAuthType::class, [
                'property_path' => 'picture_blob',
                'required'      => false,
            ])
            ->add('summary', TextType::class, [
                'required' => false,
            ])
            ->add('importance', IntegerType::class, [
                'empty_data' => '0',
                'required'   => false,
            ])
            ->add('labels', LabelsCollectionType::class, [
                'labels_class'   => LabelOrganization::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'organization',
                'required'       => false,
            ])
            ->add('user_groups', UsergroupsType::class, [
                'is_agent_group' => false,
                'owner'          => $builder->getData(),
                'required'       => false,
            ])
            ->add('email_domains', OrganizationEmailDomainsType::class, [
                'owner'          => $builder->getData(),
                'error_bubbling' => false,
                'required'       => false,
            ])
            ->add('fields', CombinedType::class, [
                'forms'          => $this->getCustomDataFields($options),
                'error_bubbling' => false,
                'required'       => false,
            ])
            ->add('contact_data', ContactDataType::class, [
                'owner'          => $builder->getData(),
                'parent_builder' => $builder,
                'required'       => false,
            ])
            ->add('members', EntityType::class, [
                'class'        => Person::class,
                'multiple'     => true,
                'by_reference' => false,
                'required'     => false,
            ])
        ;

        // resolve field name aliases
        $fieldNameResolver = $this->field_manager->getFieldNameResolver(CustomDefOrganization::class);
        $builder->addEventSubscriber($fieldNameResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => Organization::class,
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
        $defs   = $this->field_manager->getAvailableOrganizationDefs();
        $fields = [];

        foreach ($defs as $def) {
            $fields[] = [
                'name'    => $def->getId(),
                'type'    => CustomDataType::class,
                'options' => [
                    'custom_def'      => $def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => $options['agent_interface'],
                    'inline'          => true,
                ],
            ];
        }

        return $fields;
    }
}
