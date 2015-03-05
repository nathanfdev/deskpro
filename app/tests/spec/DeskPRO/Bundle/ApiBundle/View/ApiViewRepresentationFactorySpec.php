<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\DeskPRO\Bundle\ApiBundle\View;

use Pagerfanta\Pagerfanta;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\ApiBundle\View\ApiViewRepresentationFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * @mixin \DeskPRO\Bundle\ApiBundle\View\ApiViewRepresentationFactory
 */
class ApiViewRepresentationFactorySpec extends ObjectBehavior
{
    function it_returns_a_view_representation_for_an_object(\stdClass $object)
    {
        $view_representation = $this->createRepresentation($object);

        $view_representation->shouldHaveType('DeskPRO\Bundle\ApiBundle\View\Representation\StandardRepresentation');
        $view_representation->getData()->shouldBe($object);
    }

    function it_returns_a_view_representation_for_an_array()
    {
        $view_representation = $this->createRepresentation($array = array('foo' => 'bar'));

        $view_representation->shouldHaveType('DeskPRO\Bundle\ApiBundle\View\Representation\StandardRepresentation');
        $view_representation->getData()->shouldBe($array);
    }

    function it_returns_a_view_representation_for_a_pager(
        Pagerfanta $pager
    )
    {
        $current_page_results = array(
            'homer' => array('name' => 'homer'),
            'john' => array('name' => 'john'),
            'jacob' => array('name' => 'jacob'),
            'sally' => array('name' => 'sally'),
            'mario' => array('name' => 'mario'),
        );

        $pager->getCurrentPageResults()->willReturn($current_page_results);
        $pager->getCurrentPage()->willReturn(3);
        $pager->getNbPages()->willReturn(41);
        $pager->getNbResults()->willReturn(204);

        $view_representation = $this->createRepresentation($pager);

        $view_representation->shouldHaveType('DeskPRO\Bundle\ApiBundle\View\Representation\StandardRepresentation');
        $view_representation->getData()->shouldBeLike($current_page_results);

        $view_representation->getMeta()->shouldBeLike(
            array(
                'count' => 5,
                'total_count' => 204,
                'page' => 3,
                'total_pages' => 41
            )
        );
    }

    function it_can_generate_a_batch_representation_of_responses(
        Response $response1,
        Response $response2
    )
    {
        $responses = array('r1' => $response1, 'r2' => $response2);

        $view_representation = $this->createBatchRepresentation($responses);

        $view_representation->shouldHaveType('DeskPRO\Bundle\ApiBundle\View\Representation\BatchRepresentation');
        $view_representation->getResponses()->shouldBeLike($responses);
    }
}
