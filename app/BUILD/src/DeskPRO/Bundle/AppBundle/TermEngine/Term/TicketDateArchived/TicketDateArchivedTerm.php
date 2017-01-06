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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateArchived;

use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TicketDateArchivedTerm extends AbstractTerm
{
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'date'        => null,
                'date2'       => null,
                'ignore_time' => false,
            ]
        );

        $date_normalizer = function ($options, $date) {
            if (!$date) {
                return;
            }

            if ($date instanceof \DateTime || $date instanceof \DateTimeZone) {
                return $date;
            }

            return new \DateTime((string) $date);
        };

        $resolver->setNormalizers(
            [
                'date'        => $date_normalizer,
                'date2'       => $date_normalizer,
                'ignore_time' => function ($options, $ignore_time) {
                    return (bool) $ignore_time;
                },
            ]
        );

        $resolver->setConstraints(
            [
                'date' => [
                    new Assert\NotNull(),
                    new Assert\DateTime(),
                ],
                'date2' => [
                    new Assert\DateTime(),
                ],
            ]
        );
    }

    public function getSupportedOps()
    {
        return [
            TermInterface::OP_IS,
            TermInterface::OP_NOT,
            TermInterface::OP_GT,
            TermInterface::OP_GTE,
            TermInterface::OP_LT,
            TermInterface::OP_LTE,
            TermInterface::OP_NOT_RANGE,
            TermInterface::OP_RANGE,
        ];
    }

    public function getDefaultOp()
    {
        return TermInterface::OP_IS;
    }
}
