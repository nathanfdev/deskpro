<?php

namespace Application\DeskPRO\Form\Type;

use Application\DeskPRO\Form\EventListener\ResizeFormListener;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DpCategoryBuilderType extends CollectionType
{
    const EVENT_MANAGE = 'manage';

    /**
     * @var ResizeFormListener
     */
    protected $listener;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * DpCategoryBuilderType constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ResizeFormListener(
            $options['type'],
            $options['options'],
            $options['allow_add'],
            $options['allow_delete'],
            $options['delete_empty'],
            $this->em
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'dp_category_builder';
    }
}
