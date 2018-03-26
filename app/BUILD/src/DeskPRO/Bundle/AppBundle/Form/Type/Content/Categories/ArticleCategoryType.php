<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories;

use Application\DeskPRO\Entity\ArticleCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ArticleCategoryType.
 */
class ArticleCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent', EntityType::class, [
                'class'    => ArticleCategory::class,
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArticleCategory::class,
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
