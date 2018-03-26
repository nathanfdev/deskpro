<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Departments\Form\Type;

use Application\DeskPRO\Departments\TicketDepartmentEdit;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Form\Type\PermissionRowType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketDepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('department', new TicketDepartmentPropsType());
        $builder->add('permissions', 'collection', [
            'type'         => new PermissionRowType(),
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
        $builder->add('move_department', 'entity', [
            'class'         => Department::class,
            'required'      => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('d')->where('d.is_tickets_enabled = true AND d.parent IS NULL')->orderBy('d.display_order', 'ASC');
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => TicketDepartmentEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'department_edit';
    }
}
