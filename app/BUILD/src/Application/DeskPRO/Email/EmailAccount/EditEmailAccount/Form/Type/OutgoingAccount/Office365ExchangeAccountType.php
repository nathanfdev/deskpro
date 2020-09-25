<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\Office365ExchangeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Office365ExchangeAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('host', 'text', ['required' => true]);
        $builder->add('user', 'text', ['required' => true]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
        $builder->add('client_id', TextType::class, ['required' => true]);
        $builder->add('client_secret', TextType::class, ['required' => true]);
        $builder->add('token', TextType::class, ['required' => true]);
        $builder->add('refreshToken', TextType::class, ['required' => true]);
        $builder->add('type', ChoiceType::class, [
            'required'          => true,
            'choices'           => [\Application\DeskPRO\Email\EmailAccount\IncomingAccount\Office365ExchangeConfig::TYPE_POP3, Office365ExchangeConfig::TYPE_OAUTH],
            'choices_as_values' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Office365ExchangeConfig::class,
        ]);
    }

    public function getName()
    {
        return 'out_office365_exchange_account';
    }
}
