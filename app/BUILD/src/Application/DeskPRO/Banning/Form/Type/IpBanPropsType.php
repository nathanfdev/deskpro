<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning\Form\Type;

use Application\DeskPRO\Entity\BanIp;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpBanPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('banned_ip', 'text', ['required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => BanIp::class,
            ]
        );
    }

    public function getName()
    {
        return 'ip_ban';
    }
}
