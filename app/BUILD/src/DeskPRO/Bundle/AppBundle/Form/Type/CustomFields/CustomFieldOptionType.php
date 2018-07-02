<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldOptionType.
 */
class CustomFieldOptionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('display_order', IntegerType::class, [
                'required'   => false,
                'empty_data' => '0',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('parent')
            ->setAllowedTypes('parent', CustomDefAbstract::class)
            ->setNormalizer('data_class', function (Options $options) {
                return get_class($options['parent']);
            })
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $options = $form->getConfig()->getOptions();
        if (isset($data['children'])) {
            $form->add('children', CollectionType::class, [
                'required'      => false,
                'mapped'        => false,
                'entry_type'    => self::class,
                'entry_options' => [
                    'data_class' => $options['data_class'],
                    'parent'     => $options['parent'],
                ],
                'allow_add'    => true,
                'allow_delete' => true,
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!$data instanceof CustomDefAbstract) {
            return;
        }

        // set the choice's def
        /** @var CustomDefAbstract $parent */
        $parent = $form->getConfig()->getOption('parent');

        $realParent = $parent->getParent() ?: $parent;
        $data->setParent($realParent);
        $realParent->addChild($data);

        if ($parent->getParent()) {
            $data->setOption('parent_id', $parent);
        }

        // set relation to hierarchy choices
        if ($form->has('children')) {
            foreach ($form->get('children')->all() as $childForm) {
                if ($childForm->getData() instanceof CustomDefAbstract) {
                    $childForm->getData()->setOption('parent_id', $data);
                }
            }
        }
    }
}
