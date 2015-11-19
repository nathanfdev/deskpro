<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\LabelTicket;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TicketType.
 */
class TicketType extends ApiType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', 'text')
            ->add('department', 'entity', ['class' => 'DeskPRO:Department'])
            ->add('parent_ticket', 'entity', ['class' => 'DeskPRO:Ticket'])
            ->add('language', 'entity', ['class' => 'DeskPRO:Language'])
            ->add('category', 'entity', ['class' => 'DeskPRO:TicketCategory'])
            ->add('priority', 'entity', ['class' => 'DeskPRO:TicketPriority'])
            ->add('workflow', 'entity', ['class' => 'DeskPRO:TicketWorkflow'])
            ->add('person', 'entity', ['class' => 'DeskPRO:Person'])
            ->add('agent', 'entity', ['class' => 'DeskPRO:Person'])
            ->add('agent_team', 'entity', ['class' => 'DeskPRO:AgentTeam'])
            ->add('organization', 'entity', ['class' => 'DeskPRO:Organization'])
            ->add('status', 'text')
            ->add('hidden_status', 'text')
            ->add('is_hold', 'api_boolean')
            ->add('urgency', 'number')
            ->add('labels', 'api_labels_collection', [
                'labels_class'   => LabelTicket::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'ticket',
            ])
        ;
    }
}
