<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\DeskPRO\Entity\CustomDataTicket;
use Application\FormBundle\Form\FormFieldManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomDataTicketType extends AbstractType
{
    /**
     * @var \Application\FormBundle\Form\FormFieldManager
     */
    private $field_manager;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(FormFieldManager $field_manager, EntityManager $em)
    {
        $this->field_manager = $field_manager;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preDataEvent'));
        $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'postSubmitEvent'));
        $builder->addEventListener(FormEvents::SUBMIT, array($this, 'submitEvent'));
    }

    public function preDataEvent(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\CustomDataTicket $custom_data */
        $custom_data = $event->getData();
        $form = $event->getForm();
        $config = $form->getConfig();
        /** @var \Application\DeskPRO\Entity\CustomDefTicket $custom_data_field */
        $custom_data_field = $custom_data ? $custom_data->field : $config->getOption('custom_data_field');

        list($value_name, $form_type, $options) = $this->field_manager->getCustomTicketField($custom_data_field);
        $form->add($value_name, $form_type, $options);

    }

    public function submitEvent(FormEvent $event)
    {
        $config = $event->getForm()->getConfig();
        /** @var \Application\DeskPRO\Entity\CustomDataTicket $custom_data */
        $custom_data = $event->getData();
        $field = $config->getOption('custom_data_field');
        $ticket = $config->getOption('ticket');
        $custom_data->field = $field;
        $custom_data->ticket = $ticket;
    }

    public function postSubmitEvent(FormEvent $event)
    {
        // after successful form submission, we want to make sure this entity is persisted in case it is new
        /** @var \Application\DeskPRO\Entity\CustomDataTicket $custom_data */
        $custom_data = $event->getData();
        $this->em->persist($custom_data);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Application\DeskPRO\Entity\CustomDataTicket'
        ));
        $resolver->setRequired(array(
            'custom_data_field',
            'ticket'
        ));
        $resolver->setAllowedTypes(array(
            'custom_data_field' => 'Application\DeskPRO\Entity\CustomDefTicket',
            'ticket' => 'Application\DeskPRO\Entity\Ticket'
        ));
    }

    public function getName()
    {
        return 'deskpro_custom_data_ticket';
    }
}
