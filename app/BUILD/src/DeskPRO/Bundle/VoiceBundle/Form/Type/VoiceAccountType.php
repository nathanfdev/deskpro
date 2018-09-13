<?php

namespace DeskPRO\Bundle\VoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class VoiceAccountType.
 */
class VoiceAccountType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account_name', TextType::class, [
                'property_path' => 'accountName',
                'required'      => true,
            ])
            ->add('account_id', TextType::class, [
                'property_path' => 'accountId',
                'required'      => true,
            ])
            ->add('auth_token', TextType::class, [
                'property_path' => 'authToken',
                'required'      => true,
            ])
        ;
    }
}
