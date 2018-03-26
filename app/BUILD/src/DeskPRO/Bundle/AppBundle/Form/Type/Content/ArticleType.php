<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\ObjectLang\ObjectLangCollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ArticleType.
 */
class ArticleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title_translations', ObjectLangCollectionType::class, [
                'mapped'    => false,
                'prop_name' => 'title',
                'owner'     => $builder->getData(),
                'required'  => false,
            ])
            ->add('content_translations', ObjectLangCollectionType::class, [
                'mapped'    => false,
                'prop_name' => 'content',
                'owner'     => $builder->getData(),
                'required'  => false,
            ])
            ->add('categories', EntityType::class, [
                'class'    => ArticleCategory::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
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
