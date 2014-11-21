<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketType extends AbstractType
{
    public function __construct()
    {
        // inject managers to get the form type configs
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preDataEvent'));
    }

    public function preDataEvent(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $event->getData();
        $form = $event->getForm();
        /** @var \Application\DeskPRO\Entity\TicketLayout $layout */
        $layout = $form->getConfig()->getOption('layout_type');
        $layout_type = $form->getConfig()->getOption('layout_type');

        //...begin!
        $form->add('subject', 'text');
        $form->add('submit', 'submit');
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'layout_type' => 'new',
            'data_class' => 'Application\\DeskPRO\\Entity\\Ticket'
        ));
        $resolver->setRequired(array(
            'ticket_layout'
        ));
        $resolver->addAllowedValues(array(
            'layout_type' => array('new', 'edit', 'view')
        ));
        $resolver->setAllowedTypes(array(
            'ticket_layout' => 'Application\\DeskPRO\\Entity\\TicketLayout'
        ));
    }


    public function getName()
    {
        return 'ticket';
    }
}
