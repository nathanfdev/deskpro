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

namespace DpTest\DeskPRO\Bundle\AppBundle\ActionEngine\Utils;

use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\SingleActionApplicatorInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionToJsonTransformer;
use Doctrine\ORM\EntityManager;
use DpTest\DeskProTestCase;

class ActionToJsonTransformerTest extends DeskProTestCase
{
    public static $serializedArray = [
        'type'    => 'set_type',
        'options' => ['id' => 1],
    ];

    public static $wrongSerializedArray = [
        'type'    => 'something',
        'options' => ['id' => 1],
    ];

    /**
     * @test
     */
    public function it_should_be_instantiable()
    {
        $transformer = $this->instance();
        $this->assertInstanceOf(ActionToJsonTransformer::class, $transformer);
    }

    /**
     * @test
     */
    public function arrayToActionApplicator_should_return_SingleActionApplicatorInterface()
    {
        $transformer = $this->instance();
        $action      = $transformer->arrayToActionApplicator('Feedback', self::$serializedArray);
        $this->assertInstanceOf(SingleActionApplicatorInterface::class, $action);
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\AppBundle\ActionEngine\Exception\ActionApplicatorDoesNotExists
     */
    public function arrayToActionApplicator_should_raise_ActionApplicatorDoesNotExists_on_wrong_action()
    {
        $transformer = $this->instance();

        return $transformer->arrayToActionApplicator('Feedback', self::$wrongSerializedArray);
    }

    private function instance()
    {
        $em = $this->prophesize(EntityManager::class);

        return new ActionToJsonTransformer($em->reveal());
    }
}
