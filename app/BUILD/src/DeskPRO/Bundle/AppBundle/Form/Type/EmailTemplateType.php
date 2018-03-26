<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class EmailTemplateType.
 */
class EmailTemplateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextareaType::class, [
                'required' => true,
            ])
            ->add('body', TextareaType::class, [
                'filter_clean' => false,
                'required'     => true,
            ])
            ->add('create_new', HiddenType::class, [
                'required' => false,
            ])
        ;
    }
}
