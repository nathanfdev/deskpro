<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent;

use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PopupQueryType.
 */
class PopupQueryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('term', TextType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('op', TextType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('val', JsonArrayType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])

        ;
    }
}
