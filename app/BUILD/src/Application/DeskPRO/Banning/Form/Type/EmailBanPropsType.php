<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning\Form\Type;

use Application\DeskPRO\Entity\BanEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailBanPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('banned_email', 'text', ['required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => BanEmail::class,
            ]
        );
    }

    public function getName()
    {
        return 'email_ban';
    }
}
