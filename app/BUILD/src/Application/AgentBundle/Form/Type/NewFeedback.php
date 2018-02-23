<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
            'required' => false
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
