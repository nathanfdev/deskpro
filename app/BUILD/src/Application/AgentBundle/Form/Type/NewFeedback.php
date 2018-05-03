<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Type;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class NewFeedback extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //------------------------------
        // Basic fields
        //------------------------------

        $builder->add('title', 'text');
        $builder->add('content', 'textarea', ['filter_clean' => false]);

        $builder->add('category_id', 'text');
        $builder->add('status_code', 'text');

        $builder->add('labels', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('attach_ids', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('linked_ticket', EntityType::class, [
            'class'    => Ticket::class,
            'required' => false,
        ]);
        $builder->add('is_subscribe_ticket_owner', CheckboxType::class, [
            'required' => false,
        ]);
        $builder->add('is_subscribe_ticket_participants', CheckboxType::class, [
            'required' => false,
        ]);
        $builder->add('person', PersonAssignType::class, [
            'required' => false,
        ]);
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => 'Application\\AgentBundle\\Form\\Model\\NewFeedback',
        ];
    }

    public function getName()
    {
        return 'newfeedback';
    }
}
