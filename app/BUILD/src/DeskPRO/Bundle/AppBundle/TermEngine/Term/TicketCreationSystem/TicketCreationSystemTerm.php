<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketCreationSystemTerm.
 */
class TicketCreationSystemTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'creation_system' => null,
        ]);

        $resolver->setConstraints([
            'creation_system' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Choice([
                    'choices' => [
                        Ticket::CREATED_WEB_PERSON,
                        Ticket::CREATED_WEB_PERSON_PORTAL,
                        Ticket::CREATED_WEB_PERSON_WIDGET,
                        Ticket::CREATED_WEB_PERSON_EMBED,
                        Ticket::CREATED_WEB_AGENT,
                        Ticket::CREATED_WEB_AGENT_PORTAL,
                        Ticket::CREATED_WEB_API,
                        Ticket::CREATED_WEB_API_PERSON,
                        Ticket::CREATED_WEB_API_AGENT,
                        Ticket::CREATED_GATEWAY_PERSON,
                        Ticket::CREATED_GATEWAY_AGENT,
                    ],
                ]),
            ],
        ]);

        $resolver->setNormalizer(
            'creation_system',
            function ($options, $value_array) {
                if (!is_array($value_array)) {
                    $value_array = [$value_array];
                }

                return array_map('strtolower', $value_array);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedOps()
    {
        return [
            TermInterface::OP_IS,
            TermInterface::OP_NOT,
            TermInterface::OP_HAS,
            TermInterface::OP_NOT_HAS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOp()
    {
        return TermInterface::OP_IS;
    }
}
