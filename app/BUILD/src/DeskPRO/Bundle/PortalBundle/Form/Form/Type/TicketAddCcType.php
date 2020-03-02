<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketAddCcType.
 */
class TicketAddCcType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
            ->add('email', EmailType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Email(['strict' => true]),
                ],
            ])
        ;
    }
}
