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

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomFieldDefinition;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\CustomDefHierarchyNodeTransformer;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\StringToIntegerArrayTransformer;
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
        $builder->addModelTransformer(new CustomDefHierarchyNodeTransformer($options['choice_list'], $options['multiple']), true);
        if ($options['multiple']) {
            $builder->addModelTransformer(new StringToIntegerArrayTransformer(','));
        }
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
                'choice_list'        => function (Options $options) {
                    return $this->hierarchy
                        ->generateForCustomPerFormField($options['custom_field'], $options['contextual_choices'])
                        ->getChoiceList()
                    ;
                },
            ])
            ->setRequired('custom_field')
            ->setAllowedTypes('custom_field', CustomFieldDefinition::class)
        ;
    }
}
