<?php

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

        $agentTerm = new AgentTerm(['agent_ids' => [5, 6]]);
        $composite->addTerm($agentTerm);

        $depTerm = new DepartmentTerm(['department_ids' => [5, 9]]);
        $composite->addTerm($depTerm);

        $result = $this->toJson($composite);

        $result_array = json_decode($result->getWrappedObject(), true);

        expect($result_array['type'])->toBe('composite');
        expect($result_array['op'])->toBe(TermInterface::OP_OR);
        expect($result_array['options'])->toBe([]);
        expect($result_array['terms'])->toBeLike(
            [
                [
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_IS,
                    'options' => [
                        'agent_ids' => [5, 6],
                    ],
                ],
                [
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => [
                        'department_ids' => [5, 9],
                    ],
                ],
            ]
        );
    }

    public function it_can_take_a_serialized_term_and_reconstruct_the_terms()
    {
        $serialized_array = [
            'type'    => 'composite',
            'op'      => TermInterface::OP_AND,
            'options' => [],
            'terms'   => [
                [
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_NOT,
                    'options' => [
                        'agent_ids' => [5, 6],
                    ],
                ],
                [
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => [
                        'department_ids' => [5, 9],
                    ],
                ],
            ],
        ];

        $json = json_encode($serialized_array);

        $result_term = $this->toTerm($json);

        $result_term->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm');
        $result_term->getOp()->shouldBe(TermInterface::OP_AND);
        $result_term->getRawOptions()->shouldBe([]);
        $result_term->getTerms()->shouldHaveCount(2);

        $terms = $result_term->getTerms();

        $term1 = $terms[0];

        $term1->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm');
        $term1->getOp()->shouldBe(TermInterface::OP_NOT);
        $term1->getRawOptions()->shouldBe(
            [
                'agent_ids' => [5, 6],
            ]
        );

        $term2 = $terms[1];

        $term2->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm');
        $term2->getOp()->shouldBe(TermInterface::OP_IS);
        $term2->getRawOptions()->shouldBe(
            [
                'department_ids' => [5, 9],
            ]
        );
    }

    public function it_can_give_you_an_array_for_a_term_instead_of_json()
    {
        $composite = new CompositeTerm();

        $agentTerm = new AgentTerm(['agent_ids' => [5, 6]]);
        $composite->addTerm($agentTerm);

        $depTerm = new DepartmentTerm(['department_ids' => [5, 9]]);
        $composite->addTerm($depTerm);

        $result_array = $this->termToArray($composite);
        $result_array = $result_array->getWrappedObject();

        expect($result_array['type'])->toBe('composite');
        expect($result_array['op'])->toBe(TermInterface::OP_OR);
        expect($result_array['options'])->toBe([]);
        expect($result_array['terms'])->toBeLike(
            [
                [
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_IS,
                    'options' => [
                        'agent_ids' => [5, 6],
                    ],
                ],
                [
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => [
                        'department_ids' => [5, 9],
                    ],
                ],
            ]
        );
    }

    public function it_can_take_an_array_and_create_your_term()
    {
        $serialized_array = [
            'type'    => 'composite',
            'op'      => TermInterface::OP_AND,
            'options' => [],
            'terms'   => [
                [
                    'type'    => 'agent',
                    'op'      => TermInterface::OP_NOT,
                    'options' => [
                        'agent_ids' => [5, 6],
                    ],
                ],
                [
                    'type'    => 'department',
                    'op'      => TermInterface::OP_IS,
                    'options' => [
                        'department_ids' => [5, 9],
                    ],
                ],
            ],
        ];

        $result_term = $this->arrayToTerm($serialized_array);

        $result_term->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm');
        $result_term->getOp()->shouldBe(TermInterface::OP_AND);
        $result_term->getRawOptions()->shouldBe([]);
        $result_term->getTerms()->shouldHaveCount(2);

        $terms = $result_term->getTerms();

        $term1 = $terms[0];

        $term1->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm');
        $term1->getOp()->shouldBe(TermInterface::OP_NOT);
        $term1->getRawOptions()->shouldBe(
            [
                'agent_ids' => [5, 6],
            ]
        );

        $term2 = $terms[1];

        $term2->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm');
        $term2->getOp()->shouldBe(TermInterface::OP_IS);
        $term2->getRawOptions()->shouldBe(
            [
                'department_ids' => [5, 9],
            ]
        );
    }
}
