<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyChoiceLoader;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\HierarchyNodeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EntityHierarchyType.
 */
class EntityHierarchyType extends AbstractType
{
    /**
     * @var HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchyGenerator
     */
    public function __construct(HierarchyGenerator $hierarchyGenerator)
    {
        $this->hierarchyGenerator = $hierarchyGenerator;
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('choice_loader')
            ->setAllowedTypes('choice_loader', [HierarchyChoiceLoader::class])
            ->setDefaults([
                'choices_as_values'   => true,
                'hierarchy_generator' => $this->hierarchyGenerator,
                'choice_label'        => function ($value) {
                    return (string) $value;
                },
            ])
        ;
    }
}
