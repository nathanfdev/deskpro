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

namespace DpTest\DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Feedback;

use DeskPRO\Bundle\AppBundle\ActionEngine\ActionCollection\ActionCollection;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionCollectionApplicatorInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Feedback\FeedbackApplicator;
use Doctrine\ORM\EntityManager;
use DpTest\DeskProTestCase;

class FeedbackApplicatorTest extends DeskProTestCase
{
    public static $options = [
        'actions' => [
            'set_type' => '1',
            'approve'  => [],
        ],
    ];
    public static $ids = [1, 2, 3];

    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $applicator = $this->instance();
        $this->assertInstanceOf(FeedbackApplicator::class, $applicator);
        $this->assertInstanceOf(ActionCollectionApplicatorInterface::class, $applicator);
    }

    /**
     * @test
     */
    public function prepareActions_should_return_ActionCollection()
    {
        $applicator = $this->instance();
        $actions    = $applicator->prepareActions();
        $this->assertInstanceOf(ActionCollection::class, $actions);
    }

    private function instance()
    {
        $em = $this->prophesize(EntityManager::class);

        return new FeedbackApplicator($em->reveal(), self::$options);
    }
}
