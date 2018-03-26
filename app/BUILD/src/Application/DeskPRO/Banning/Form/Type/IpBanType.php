<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning\Form\Type;

use Application\DeskPRO\Banning\IpBanEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpBanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ip_ban', new IpBanPropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => IpBanEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'ip_ban_edit';
    }
}
