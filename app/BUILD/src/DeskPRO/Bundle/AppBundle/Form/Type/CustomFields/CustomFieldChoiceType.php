<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyChoiceLoader;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer\HierarchyNodeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldChoiceType.
 */
class CustomFieldChoiceType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchy
     */
    public function __construct(HierarchyGenerator $hierarchy)
    {
        $this->hierarchyGenerator = $hierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['multiple']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onTransformSingleChoice'], 100);

            if ($options['expanded']) {
                // for radio boxes ChoiceType uses PRE_SET_DATA callback,
                // so we need to transform our choice to HierarchyNode before it called
                $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onTransformRadioData'], 100);
            }
        }

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
            ->setDefaults([
                'empty_data'        => null,
                'choices_as_values' => true,
                'choice_loader'     => function (Options $options) {
                    return $this->hierarchyGenerator->generateForCustomFormField($options['custom_field'])
                        ->getChoiceLoader();
                },
                'choice_label' => function ($value) {
                    return (string) $value;
                },
                'placeholder' => '',
                'help'        => '',
            ])
            ->setRequired('custom_field')
            ->setAllowedTypes('custom_field', CustomDefAbstract::class)
            ->setAllowedTypes('choice_loader', [HierarchyChoiceLoader::class])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onTransformSingleChoice(FormEvent $event)
    {
        $data = $event->getData();
        if (is_array($data)) {
            $data = (string) array_shift($data);
        }

        $event->setData($data);
    }

    /**
     * @param FormEvent $event
     */
    public function onTransformRadioData(FormEvent $event)
    {
        $transformer = new HierarchyNodeTransformer();
        $event->setData($transformer->transform($event->getData()));
    }
}
