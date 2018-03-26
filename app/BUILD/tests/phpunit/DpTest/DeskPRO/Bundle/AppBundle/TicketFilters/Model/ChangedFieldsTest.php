<?php

<<<<<<< HEAD:app/BUILD/tests/phpunit/DpTest/DeskPRO/Bundle/AppBundle/TicketFilters/Model/ChangedFieldsTest.php
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

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;
=======
/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler;
>>>>>>> origin/develop:app/BUILD/src/DeskPRO/Bundle/AppBundle/TermEngine/Engine/Php/TermCompiler/PhpTermCompilerFactory.php

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;

class ChangedFieldsTest extends \PHPUnit_Framework_TestCase
{
    public function testTicketChanges()
    {
        $ticketA            = new TicketModel();
        $ticketA->id        = 1;
        $ticketA->followers = [1, 2];
        $ticketA->labels    = ['foo'];

        $ticketB            = new TicketModel();
        $ticketB->id        = 2;
        $ticketA->followers = [1, 2, 3];
        $ticketA->labels    = ['foo', 'bar'];

        $diff = $ticketA->getChangedFields($ticketB);

        $this->assertEquals(
            ['ticket.id', 'ticket.followers', 'ticket.labels'],
            $diff
        );
    }
}
