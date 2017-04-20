<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
