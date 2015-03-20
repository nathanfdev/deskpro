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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Util;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\AppBundle\TermEngine\Util\TermToJsonConverter;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Util\TermToJsonConverter
 */
class TermToJsonConverterSpec extends ObjectBehavior
{
    function it_converts_a_term_into_json()
    {
        $composite = new CompositeTerm();

        $agentTerm = new AgentTerm(array('agent_ids' => array(5, 6)));
        $composite->addTerm($agentTerm);

        $depTerm = new DepartmentTerm(array('department_ids' => array(5, 9)));
        $composite->addTerm($depTerm);

        $result = $this->toJson($composite);

        $result_array = json_decode($result->getWrappedObject(), true);

        expect($result_array['class'])->toBe('DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm');
        expect($result_array['serialized'])->toBe(
            array(
                'op' => TermInterface::OP_OR,
                'options' => array()
            )
        );
        expect($result_array['terms'])->toBeLike(
            array(
                array(
                    'class' => 'DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTerm',
                    'serialized' => array(
                        'op' => TermInterface::OP_IS,
                        'options' => array(
                            'agent_ids' => array(5, 6)
                        )
                    )
                ),
                array(
                    'class' => 'DeskPRO\Bundle\AppBundle\TermEngine\Term\DepartmentTerm',
                    'serialized' => array(
                        'op' => TermInterface::OP_IS,
                        'options' => array(
                            'department_ids' => array(5, 9)
                        )
                    )
                )
            )
        );
    }
}
