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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\TermEngine\OptionsResolver\TermOptionsResolver;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketFlaggedTerm.
 */
class TicketFlaggedTerm extends AbstractTerm
{
    /**
     * {@inheritdoc}
     */
    public static function configureOptions(TermOptionsResolver $resolver)
    {
        $resolver->setConstraints([
            'flag' => [
                new Assert\NotBlank(),
            ],
        ]);
        $resolver->setNormalizer(
            'flag',
            function ($options, $value) {
                if (!is_array($value)) {
                    $value = [$value];
                }

                $value = array_map('strtolower', $value);
                $colors = [];
                foreach ($value as $color) {
                    if (is_numeric($color)) {
                        if (isset(TicketFlagged::$colorMap[$color])) {
                            $colors[] = TicketFlagged::$colorMap[$color];
                        }
                    } else {
                        $colors[] = $color;
                    }
                }

                return $colors;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedOps()
    {
        return [TermInterface::OP_IS, TermInterface::OP_NOT];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOp()
    {
        return TermInterface::OP_IS;
    }
}
