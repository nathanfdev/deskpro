<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractTicketParticipantType.
 */
abstract class AbstractTicketParticipantType extends AbstractType
{
    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(SettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('allow_all', function (Options $options) {
                // no validation for agents
                if ($options['agent_interface']) {
                    return true;
                }

                // process agents that are in the CC line of incoming emails
                return (bool) $this->settingsResolver->getGlobalSettings()->get('core_tickets.add_agent_ccs');
            })
            ->setRequired(['owner', 'agent_interface'])
            ->setAllowedTypes('owner', Ticket::class)
            ->setAllowedTypes('agent_interface', 'boolean')
        ;
    }
}
