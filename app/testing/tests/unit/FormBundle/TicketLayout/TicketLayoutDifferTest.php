<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace DpUnitTests\FormBundle\TicketLayout;

use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use Application\FormBundle\FormFields;
use Application\FormBundle\TicketLayout\TicketLayoutDiffer;

class TicketLayoutDifferTest extends \DpUnitTestCase
{
    public function testFindRemovalFields()
    {
        $layout1 = new Layout();
        $layout2 = new Layout();

        $field1 = new LayoutField(FormFields::SUBJECT);
        $field2 = new LayoutField(FormFields::MESSAGE);
        $field3 = new LayoutField(FormFields::USER_EMAIL);
        $field4 = new LayoutField(FormFields::CC);
        $field5 = new LayoutField(FormFields::CUSTOM_FIELD, 5);

        $layout1->setAll(array(
            $field1,
            $field2,
            $field3,
            $field4,
            $field5
        ));

        $layout2->setAll(array(
            $field2,
            $field5
        ));

        $differ = new TicketLayoutDiffer();
        $removables = $differ->findFieldsToRemove($layout1, $layout2);

        $this->assertEquals(array($field1, $field3, $field4), $removables, 'returns the correct fields to remove');
    }

    public function testFindRemovalFieldsIsEmptyWhenNoRemovalNeeded()
    {
        $layout1 = new Layout();
        $layout2 = new Layout();

        $field1 = new LayoutField(FormFields::SUBJECT);
        $field2 = new LayoutField(FormFields::MESSAGE);
        $field3 = new LayoutField(FormFields::USER_EMAIL);
        $field4 = new LayoutField(FormFields::CC);
        $field5 = new LayoutField(FormFields::CUSTOM_FIELD, 5);

        $layout1->setAll(array(
            $field1,
            $field3,
            $field4
        ));

        $layout2->setAll(array(
            $field1,
            $field2,
            $field3,
            $field4,
            $field5
        ));

        $differ = new TicketLayoutDiffer();
        $removables = $differ->findFieldsToRemove($layout1, $layout2);

        $this->assertEquals(array(), $removables, 'returns the correct fields to remove');
    }

    public function testFindAdditionalFields()
    {
        $layout1 = new Layout();
        $layout2 = new Layout();

        $field1 = new LayoutField(FormFields::SUBJECT);
        $field2 = new LayoutField(FormFields::MESSAGE);
        $field3 = new LayoutField(FormFields::USER_EMAIL);
        $field4 = new LayoutField(FormFields::CC);
        $field5 = new LayoutField(FormFields::CUSTOM_FIELD, 5);

        $layout1->setAll(array(
            $field1,
            $field2,
            $field3,
            $field4,
            $field5
        ));

        $layout2->setAll(array(
            $field2,
            $field5
        ));

        $differ = new TicketLayoutDiffer();
        $additional = $differ->findFieldsToAdd($layout1, $layout2);

        $this->assertEquals(array(), $additional, 'returns the correct fields to add');
    }

    public function testFindAdditionalFieldsIsEmptyWhenNoRemovalNeeded()
    {
        $layout1 = new Layout();
        $layout2 = new Layout();

        $field1 = new LayoutField(FormFields::SUBJECT);
        $field2 = new LayoutField(FormFields::MESSAGE);
        $field3 = new LayoutField(FormFields::USER_EMAIL);
        $field4 = new LayoutField(FormFields::CC);
        $field5 = new LayoutField(FormFields::CUSTOM_FIELD, 5);

        $layout1->setAll(array(
            $field1,
            $field3,
            $field4
        ));

        $layout2->setAll(array(
            $field1,
            $field2,
            $field3,
            $field4,
            $field5
        ));

        $differ = new TicketLayoutDiffer();
        $removables = $differ->findFieldsToAdd($layout1, $layout2);

        $this->assertEquals(array($field2, $field5), $removables, 'returns the correct fields to add');
    }
}
 