<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\UserRules\Form\Type;

use Application\DeskPRO\Entity\UserRule;
use Application\DeskPRO\UserRules\Form\DataTransformer\EmailPatternsDataTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRulePropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new EmailPatternsDataTransformer();

        $builder->add(
            $builder->create('email_patterns', 'text')
                ->addModelTransformer($transformer)
        );

        $builder->add(
            'add_usergroup',
            'entity',
            [
                'class'         => 'DeskPRO:Usergroup',
                'required'      => false,
                'multiple'      => false,
                'property'      => 'title',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where(
                        'u.is_agent_group = 0 AND u.sys_name IS NULL'
                    );
                },
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => UserRule::class,
            ]
        );
    }

    public function getName()
    {
        return 'user_rule';
    }
}
