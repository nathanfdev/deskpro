<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use DeskPRO\Bundle\AppBundle\Model\PopupModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PopupType.
 */
class PopupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('external_id', TextType::class, [
                'required'      => false,
                'property_path' => 'externalId',
            ])
            // for now conditions to find target and additional info just arrays
            ->add('target', JsonArrayType::class, [
                'required'    => false,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('display', CollectionType::class, [
                'entry_type'     => PopupDisplayType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'required'       => true,
                'constraints'    => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('actions', CollectionType::class, [
                'entry_type'     => PopupActionType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
                'required'       => true,
                'constraints'    => [
                    new Assert\NotBlank(),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PopupModel::class,
            ])
        ;
    }
}
