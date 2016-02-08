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
namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\CustomDefHierarchyNodeTransformer;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\StringToIntegerArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CustomFieldChoiceType.
 */
class CustomFieldChoiceType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator
     */
    private $hierarchy_generator;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchy
     */
    public function __construct(HierarchyGenerator $hierarchy)
    {
        $this->hierarchy_generator = $hierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder->addModelTransformer(new StringToIntegerArrayTransformer(','));
        }

        $builder->addModelTransformer(new CustomDefHierarchyNodeTransformer($options['choice_list'], $options['multiple']), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_custom_field_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $hierarchy_generator = $this->hierarchy_generator;

        $resolver
            ->setDefaults([
                'empty_data'  => null,
                'choice_list' => function (Options $options) use ($hierarchy_generator) {
                        return $hierarchy_generator->generateForCustomFormField($options['custom_field'])->getChoiceList();
                    },
                'placeholder' => '',
                'help'        => '',
            ])
            ->setRequired([
                'custom_field',
            ])
            ->setAllowedTypes([
                'custom_field' => 'Application\\DeskPRO\\Entity\\CustomDefAbstract',
            ])
        ;
    }
}
