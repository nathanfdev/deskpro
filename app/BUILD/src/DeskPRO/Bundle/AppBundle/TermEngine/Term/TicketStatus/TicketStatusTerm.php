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
namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TicketStatusTerm extends AbstractTerm
{
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults(['status' => null]);

        $resolver->setConstraints(
            [
                'status' => [
                    new Assert\NotBlank(),
                    new Assert\Type('array'),
                    new Assert\Choice(
                        [
                            'multiple' => true,
                            'choices'  => [
                                Ticket::STATUS_ARCHIVED,
                                Ticket::STATUS_AWAITING_AGENT,
                                Ticket::STATUS_AWAITING_USER,
                                Ticket::STATUS_RESOLVED,
                                Ticket::HIDDEN_STATUS_DELETED,
                                Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_DELETED,
                                Ticket::HIDDEN_STATUS_SPAM,
                                Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_SPAM,
                            ],
                        ]
                    ),
                ],
            ]
        );

        $resolver->setNormalizer(
            'status',
            function ($options, $status_array) {

                if (!is_array($status_array)) {
                    $status_array = [$status_array];
                }

                // if the passed status is a "hidden" sub-status, the compilers
                // are expecting to have "hidden." prepended. We ensure that here.
                $normalized = [];

                foreach ($status_array as $status) {
                    if (in_array($status, [Ticket::HIDDEN_STATUS_SPAM, Ticket::HIDDEN_STATUS_DELETED])) {
                        $status = Ticket::STATUS_HIDDEN.'.'.$status;
                    }

                    $normalized[] = $status;
                }

                return $normalized;
            }
        );
    }

    public function getSupportedOps()
    {
        return array(TermInterface::OP_IS, TermInterface::OP_NOT);
    }

    public function getDefaultOp()
    {
        return TermInterface::OP_IS;
    }
}
