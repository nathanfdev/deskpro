<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\TwitterAccounts\Form\Type;

use Application\DeskPRO\TwitterAccounts\TwitterAccountEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwitterAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('twitter_account', new TwitterAccountPropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => TwitterAccountEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'twitter_account_edit';
    }
}
