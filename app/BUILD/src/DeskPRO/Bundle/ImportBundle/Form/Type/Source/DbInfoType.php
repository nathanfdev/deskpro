<?php

namespace DeskPRO\Bundle\ImportBundle\Form\Type\Source;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DbInfoType.
 */
class DbInfoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('host', TextType::class, [
                'empty_data'  => 'localhost',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('port', TextType::class, [
                'empty_data'  => '3306',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('user', TextType::class, [
                'empty_data'  => 'root',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('password', TextType::class)
            ->add('dbname', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('driver', TextType::class, [
                'empty_data' => 'pdo_mysql',
            ])
            ->add('charset', TextType::class, [
                'empty_data' => 'utf8',
            ])
        ;
    }
}
