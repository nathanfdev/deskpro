<?php

namespace Application\DeskPRO\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Form\Transformer\CustomFields\ChoiceDataTransformer;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\From;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends CustomFieldType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $that         = $this;
        $fieldOptions = $this->getValueOptions();

        $builder
            ->add('value', 'entity', array_merge($fieldOptions, [

                'empty_value' => !empty($fieldOptions['expanded']) ? false : 'Choose an option',

                'class'         => 'DeskPRO:CustomFieldDefinition',
                'property'      => 'title',
                'query_builder' => function (EntityRepository $er) use ($that, $options) {
                    return $that->getChoicesQueryBuilder($er, $options);
                },
            ]))
            ->addModelTransformer(new ChoiceDataTransformer($options['persister'], $options['owner']));

        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => null,
            'allow_edit' => false,
        ]);
    }

    /**
     * @param EntityRepository $er
     * @param array            $options
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getChoicesQueryBuilder(EntityRepository $er, array $options)
    {
        $def = $this->definition;

        return $er->createQueryBuilder('d')
            ->add('from', new From('DeskPRO:CustomFieldDefinition', 'd', 'd.id'), false)
            ->where('d.parent = :parent')
            ->andWhere('d.owner_class = :owner_class and d.context_class is null')
            ->orderBy('d.display_order', 'ASC')
            ->setParameter('owner_class', $def['owner_class'])
            ->setParameter('parent', $def['id']);
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        if (!$data = $form->get('value')->getData()) {
            return;
        }

        $rendered = null;

        if ($data instanceof CustomFieldDefinition) {
            $rendered = $data['title'];
        } else {
            // @var $data CustomFieldDefinition[]
            $rendered = [];
            foreach ($data as $el) {
                $rendered[] = $el['title'];
            }
            $rendered = implode(', ', $rendered);
        }

        $view->vars['rendered_data'] = $rendered;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cf_choice';
    }

    /**
     * override parent call.
     */
    public function onPostSubmit(FormEvent $event)
    {
        return;
    }
}
