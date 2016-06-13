<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\NewSettings\SettingsBag;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketMessageType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class TicketReplyType.
 */
class TicketReplyType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param LanguageManager $language_manager
     */
    public function __construct(LanguageManager $language_manager, EntityManager $em)
    {
        $this->language_manager = $language_manager;
        $this->em               = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ticket_message', TicketMessageType::class, [
                'ticket'              => $options['ticket'],
                'person'              => $options['person'],
                'ticket_message'      => $options['ticket_message'],
                'message_label'       => $options['message_label'],
                'label'               => false,
                'render_is_note'      => false,
                'format'              => 'html',
                'message_constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('attachments', 'ticket_message_attachment_collection', [
                'ticket_message' => $options['ticket_message'],
                'person'         => $options['person'],
                'label'          => false,
            ])
            ->add('more_attachments', 'submit', [
                'validation_groups' => false,
                'label'             => $this->language_manager->phrase('portal.forms.label_add_attachment'),
            ])
            ->add('submit', 'submit', [
                'label' => $this->language_manager->phrase('portal.tickets.add-reply'),
            ])
        ;
    }

    public function checkForDupes($value, ExecutionContextInterface $context)
    {
        if (!isset($value['ticket_message'])) {
            return;
        }

        /** @var Form $form */
        $form   = $context->getRoot();
        $ticket = $form->getConfig()->getOption('ticket');

        /** @var \Application\DeskPRO\EntityRepository\TicketMessage $rep */
        $rep = $this->em->getRepository('DeskPRO:TicketMessage');
        if ($rep->checkDupeMessage($value['ticket_message'], $ticket, 5 * 60)) {
            $context
                ->buildViolation('Duplicate message')
                ->atPath('ticket_message')
                ->addViolation();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_reply';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'message_label' => false,
                'constraints'   => array(
                    new Callback(array($this, 'checkForDupes')),
                ),
            ])
            ->setRequired([
                'person', 'ticket', 'ticket_message', 'settings',
            ])
            ->setAllowedTypes([
                'ticket'         => Ticket::class,
                'ticket_message' => TicketMessage::class,
                'settings'       => SettingsBag::class,
                'person'         => Person::class,
            ])
        ;
    }
}
