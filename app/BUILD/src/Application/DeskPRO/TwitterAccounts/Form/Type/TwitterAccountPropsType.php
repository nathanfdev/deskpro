<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\TwitterAccounts\Form\Type;

use Application\DeskPRO\Entity\TwitterAccount;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwitterAccountPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'persons',
            'entity',
            [
                'class'         => 'DeskPRO:Person',
                'required'      => false,
                'expanded'      => true,
                'multiple'      => true,
                'property'      => 'display_name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where(
                        'p.is_agent = true AND p.is_deleted = false'
                    );
                },
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => TwitterAccount::class,
            ]
        );
    }

    public function getName()
    {
        return 'twitter_account';
    }
}
