<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomFieldDefinition;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyChoiceLoader;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\HierarchyNodeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContextualPerFieldChoiceType.
 */
class CustomPerFieldChoiceType extends AbstractType
{
    /**
     * @var HierarchyGenerator
     */
    private $hierarchy;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchy
     */
    public function __construct(HierarchyGenerator $hierarchy)
    {
        $this->hierarchy = $hierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new HierarchyNodeTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'custom_field_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'empty_data'         => null,
                'contextual_choices' => [],
                'choice_as_values'   => true,
                'choice_loader'      => function (Options $options) {
                    return $this->hierarchy
                        ->generateForCustomPerFormField($options['custom_field'], $options['contextual_choices'])
                        ->getChoiceLoader()
                    ;
                },
                'choice_label' => function ($value) {
                    return (string) $value;
                },
            ])
            ->setRequired('custom_field')
            ->setAllowedTypes('custom_field', CustomFieldDefinition::class)
            ->setAllowedTypes('choice_loader', [HierarchyChoiceLoader::class])
        ;
    }
}
