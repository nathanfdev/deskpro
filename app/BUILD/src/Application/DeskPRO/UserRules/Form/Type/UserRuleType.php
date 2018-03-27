<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\UserRules\Form\Type;

use Application\DeskPRO\UserRules\UserRuleEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user_rule', new UserRulePropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => UserRuleEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'user_rule_edit';
    }
}
