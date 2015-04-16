<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term;

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketSubjectTerm extends AbstractTerm
{
    protected $op = TermInterface::OP_IS;

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            array(
                'subject',
            )
        );

        $resolver->setDefaults(
            array(
                'wildcard_prefix' => false,
                'wildcard_postfix' => false,
            )
        );

        $resolver->setAllowedTypes(
            array(
                'subject' => 'array',
                /**
                 * todo https://github.com/symfony/symfony/issues/12586
                 * https://github.com/symfony/symfony/commit/a0e3757bf06a42cad076f5d64f4e0904bafee64a
                 * This PR was submitted for the 2.3 branch but it was merged into the 2.7 branch instead
                 */
//                'wildcard_prefix' => 'boolean',
//                'wildcard_postfix' => 'boolean',
            )
        );
    }
}
