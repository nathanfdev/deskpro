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
namespace DpTest\DeskPRO\Bundle\AppBundle\DataSerializer;

use Application\DeskPRO\Entity\TicketAttachment;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTypeMap;
use DeskPRO\Bundle\AppBundle\Entity\SandboxWidget;
use DpTest\DeskProTestCase;

class DataTypeMapTest extends DeskProTestCase
{
    public function testMapKnowsTypeBasedOnObjectClass()
    {
        $map = $this->makeMap();

        $widget = new SandboxWidget();

        $this->assertSame('sandbox_widget', $map->findType($widget), 'works for class type');
    }

    public function testMapDefaultsToUnderscoreClassNameTypeByDefault()
    {
        $map = $this->makeMap();

        $implicit_type_because_not_in_map = new \stdClass();

        $this->assertSame('std_class', $map->findType($implicit_type_because_not_in_map));

        $implicit_type_because_not_in_map = new TicketAttachment();

        $this->assertSame('ticket_attachment', $map->findType($implicit_type_because_not_in_map));
    }

    protected function makeMap()
    {
        return new DataTypeMap(
            [
                'sandbox_widget' => [
                    'classes' => [
                        'DeskPRO\Bundle\AppBundle\Entity\SandboxWidget',
                    ],
                ],
            ]
        );
    }
}
