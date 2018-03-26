<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content\Categories;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AppBundle\Form\Type\Phrase\PhraseCollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ContentCategoryAbstractType.
 */
class ContentCategoryAbstractType extends AbstractType
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
            ->add('title_translations', PhraseCollectionType::class, [
                'prop_name' => 'title',
                'owner'     => $builder->getData(),
                'required'  => false,
            ])
            ->add('brand', EntityType::class, [
                'class'    => Brand::class,
                'required' => false,
            ])
            ->add('usergroups', EntityType::class, [
                'class'    => Usergroup::class,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
        ;
    }
}
