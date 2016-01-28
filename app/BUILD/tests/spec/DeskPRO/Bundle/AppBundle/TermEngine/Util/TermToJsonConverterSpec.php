<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Util;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Util\TermToJsonConverter
 */
class TermToJsonConverterSpec extends ObjectBehavior
{
    public function it_converts_a_term_into_json()
    {
        $composite = new CompositeTerm();

        $agentTerm = new AgentTerm(array('agent_ids' => array(5, 6)));
        $composite->addTerm($agentTerm);

        $depTerm = new DepartmentTerm(array('department_ids' => array(5, 9)));
        $composite->addTerm($depTerm);

        $result = $this->toJson($composite);

        $result_array = json_decode($result->getWrappedObject(), true);

        expect($result_array['type'])->toBe('composite');
        expect($result_array['op'])->toBe(TermInterface::OP_OR);
        expect($result_array['options'])->toBe(array());
        expect($result_array['terms'])->toBeLike(
            array(
                array(
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_IS,
                    'options' => array(
                        'agent_ids' => array(5, 6),
                    ),
                ),
                array(
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => array(
                        'department_ids' => array(5, 9),
                    ),
                ),
            )
        );
    }

    public function it_can_take_a_serialized_term_and_reconstruct_the_terms()
    {
        $serialized_array = array(
            'type'    => 'composite',
            'op'      => TermInterface::OP_AND,
            'options' => array(),
            'terms'   => array(
                array(
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_NOT,
                    'options' => array(
                        'agent_ids' => array(5, 6),
                    ),
                ),
                array(
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => array(
                        'department_ids' => array(5, 9),
                    ),
                ),
            ),
        );

        $json = json_encode($serialized_array);

        $result_term = $this->toTerm($json);

        $result_term->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm');
        $result_term->getOp()->shouldBe(TermInterface::OP_AND);
        $result_term->getRawOptions()->shouldBe(array());
        $result_term->getTerms()->shouldHaveCount(2);

        $terms = $result_term->getTerms();

        $term1 = $terms[0];

        $term1->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm');
        $term1->getOp()->shouldBe(TermInterface::OP_NOT);
        $term1->getRawOptions()->shouldBe(
            array(
                'agent_ids' => array(5, 6),
            )
        );

        $term2 = $terms[1];

        $term2->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm');
        $term2->getOp()->shouldBe(TermInterface::OP_IS);
        $term2->getRawOptions()->shouldBe(
            array(
                'department_ids' => array(5, 9),
            )
        );
    }

    public function it_can_give_you_an_array_for_a_term_instead_of_json()
    {
        $composite = new CompositeTerm();

        $agentTerm = new AgentTerm(array('agent_ids' => array(5, 6)));
        $composite->addTerm($agentTerm);

        $depTerm = new DepartmentTerm(array('department_ids' => array(5, 9)));
        $composite->addTerm($depTerm);

        $result_array = $this->termToArray($composite);
        $result_array = $result_array->getWrappedObject();

        expect($result_array['type'])->toBe('composite');
        expect($result_array['op'])->toBe(TermInterface::OP_OR);
        expect($result_array['options'])->toBe(array());
        expect($result_array['terms'])->toBeLike(
            array(
                array(
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_IS,
                    'options' => array(
                        'agent_ids' => array(5, 6),
                    ),
                ),
                array(
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => array(
                        'department_ids' => array(5, 9),
                    ),
                ),
            )
        );
    }

    public function it_can_take_an_array_and_create_your_term()
    {
        $serialized_array = array(
            'type'    => 'composite',
            'op'      => TermInterface::OP_AND,
            'options' => array(),
            'terms'   => array(
                array(
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_NOT,
                    'options' => array(
                        'agent_ids' => array(5, 6),
                    ),
                ),
                array(
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => array(
                        'department_ids' => array(5, 9),
                    ),
                ),
            ),
        );

        $result_term = $this->arrayToTerm($serialized_array);

        $result_term->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm');
        $result_term->getOp()->shouldBe(TermInterface::OP_AND);
        $result_term->getRawOptions()->shouldBe(array());
        $result_term->getTerms()->shouldHaveCount(2);

        $terms = $result_term->getTerms();

        $term1 = $terms[0];

        $term1->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm');
        $term1->getOp()->shouldBe(TermInterface::OP_NOT);
        $term1->getRawOptions()->shouldBe(
            array(
                'agent_ids' => array(5, 6),
            )
        );

        $term2 = $terms[1];

        $term2->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm');
        $term2->getOp()->shouldBe(TermInterface::OP_IS);
        $term2->getRawOptions()->shouldBe(
            array(
                'department_ids' => array(5, 9),
            )
        );
    }
}
