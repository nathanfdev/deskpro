<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories;

use Application\DeskPRO\Entity\NewsCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NewsCategoryType.
 */
class NewsCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('parent', EntityType::class, [
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
            'data_class' => NewsCategory::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ContentCategoryAbstractType::class;
    }
}
