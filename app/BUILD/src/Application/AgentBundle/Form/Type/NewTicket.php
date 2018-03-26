<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Type;

use Application\AgentBundle\Form\Model\NewTicket as NewTicketModel;
use Application\AgentBundle\Form\Model\NewTicketPerson as NewTicketPersonModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewTicket extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //------------------------------
        // User fields
        //------------------------------

        $user_builder = $builder->create('person', 'form', ['data_class' => NewTicketPersonModel::class]);
        $user_builder->add('id', 'hidden');
        $user_builder->add('name', 'text', ['required' => false]);
        $user_builder->add('email_address', 'text', ['required' => false]);
        $user_builder->add('organization', 'text', ['required' => false]);
        $user_builder->add('organization_position', 'text', ['required' => false]);
        $user_builder->add('language_id', 'text', ['required' => false]);

        $builder->add($user_builder);

        //------------------------------
        // Ticket fields
        //------------------------------

        $builder->add('subject', 'text');
        $builder->add('notify_template', 'hidden');
        $builder->add('message', 'textarea', ['filter_clean' => false]);
        $builder->add('is_html_reply', 'hidden');

        $builder->add('brand_id', 'text');
        $builder->add('department_id', 'text');
        $builder->add('status', 'text');
        $builder->add('agent_id', 'text', ['required' => false]);
        $builder->add('agent_team_id', 'text', ['required' => false]);

        $builder->add('category_id', 'text', ['required' => false]);
        $builder->add('priority_id', 'text', ['required' => false]);
        $builder->add('workflow_id', 'text', ['required' => false]);
        $builder->add('product_id', 'text', ['required' => false]);

        $builder->add('billing_type', 'hidden', ['required' => false]);
        $builder->add('billing_amount', 'hidden', ['required' => false]);
        $builder->add('billing_hours', 'hidden', ['required' => false]);
        $builder->add('billing_minutes', 'hidden', ['required' => false]);
        $builder->add('billing_seconds', 'hidden', ['required' => false]);

        $builder->add('add_cc_person', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('add_cc_newperson', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('attach', 'collection', [
            'type'         => 'hidden',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => NewTicketModel::class,
        ];
    }

    public function getName()
    {
        return 'newticket';
    }
}
