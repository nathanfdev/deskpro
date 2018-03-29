<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Content;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use DeskPRO\Bundle\AppBundle\Form\Type\BlobAuthType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DownloadType.
 */
class DownloadType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('blob', BlobAuthType::class, [
                'property_path' => 'blob',
                'required'      => false,
            ])
            ->add('category', EntityType::class, [
                'class'    => DownloadCategory::class,
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Download::class,
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
