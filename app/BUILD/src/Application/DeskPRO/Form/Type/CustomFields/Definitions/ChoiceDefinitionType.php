<?php

namespace Application\DeskPRO\Form\Type\CustomFields\Definitions;

use Application\DeskPRO\Form\Type\DpCategoryBuilderType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChoiceDefinitionType.
 */
class ChoiceDefinitionType extends CustomFieldDefinitionType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // if we need to define all properties, not only children
        if (!$options['children_only']) {
            parent::buildForm($builder, $options);
            $builder->get('options')
                ->add('multiple', 'checkbox')
                ->add('expanded', 'checkbox');
        } else {
            // called in parent
            $builder->addEventSubscriber($this);
        }

        $children = null;
        if ($options['data'] && $options['children_collection']) {
            $children = $options['children_collection']->get($options['data']['id']);
        }

        $builder
            ->add('_children', DpCategoryBuilderType::class, [
                'type'               => SimpleDefinitionType::class,
                'label'              => false,
                'allow_add'          => true,
                'allow_delete'       => true,
                'required'           => false,
                'data'               => $children ?: new ArrayCollection(),
                'mapped'             => false,
                'allow_extra_fields' => true,
                'options'            => [
                    'label'    => false,
                    'context'  => $options['context'],
                    'parent'   => $options['data'],
                    'disabled' => false,
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setDefaults([
                'children_only'       => false,
                'children_collection' => null,
            ])
            ->setDefined([
                'children_collection', 'children_only',
            ])
            ->addAllowedTypes([
                'children_collection' => ['null', ArrayCollection::class],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cf_definition_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['rendered_data'] = $form->get('_children')->count().' Options';
    }
}
