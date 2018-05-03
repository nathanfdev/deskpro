<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\License;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SupportRequestType.
 */
class SupportRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Person $person */
        $person = $options['person'];
        $builder
            ->add('name', TextType::class, [
                'empty_data' => $person->getDisplayName(),
            ])
            ->add('email', EmailType::class, [
                'empty_data'  => $person->getPrimaryEmailAddress(),
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(['strict' => true]),
                ],
            ])
            ->add('subject', TextType::class)
            ->add('message', HtmlTextareaType::class, [
                'constraints' => [
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
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
        ;
    }
}
