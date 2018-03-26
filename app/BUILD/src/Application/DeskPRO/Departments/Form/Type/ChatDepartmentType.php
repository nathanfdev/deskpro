<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments\Form\Type;

use Application\DeskPRO\Departments\ChatDepartmentEdit;
use Application\DeskPRO\Form\Type\PermissionRowType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChatDepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('department', new ChatDepartmentPropsType());
        $builder->add(
            'permissions',
            'collection',
            [
                'type'         => new PermissionRowType(),
                'allow_add'    => true,
                'allow_delete' => true,
            ]
        );
        $builder->add(
            'move_department',
            'entity',
            [
                'class'         => 'DeskPRO:Department',
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')->where(
                        'd.is_chat_enabled = true AND d.parent IS NULL'
                    )->orderBy('d.display_order', 'ASC');
                },
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => ChatDepartmentEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'department_edit';
    }
}
