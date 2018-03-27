<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning\Form\Type;

use Application\DeskPRO\Banning\EmailBanEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailBanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email_ban', new EmailBanPropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => EmailBanEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'email_ban_edit';
    }
}
