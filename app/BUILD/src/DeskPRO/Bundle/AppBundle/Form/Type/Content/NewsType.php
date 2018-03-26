<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NewsType.
 */
class NewsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'class'    => NewsCategory::class,
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ContentAbstractType::class;
    }
}
