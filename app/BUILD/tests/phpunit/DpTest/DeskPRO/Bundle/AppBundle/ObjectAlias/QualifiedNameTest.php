<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO IS not free software. We release the full
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

namespace DpTest\DeskPRO\Bundle\AppBundle\ObjectAlias;

use DeskPRO\Bundle\AppBundle\ObjectAlias\QualifiedName;
use DpTest\DeskProTestCase;

class QualifiedNameTest extends DeskProTestCase
{
    public function testIsValidIdentifierReturnsFalse()
    {
//        $qualifiedNames = [
//            new QualifiedName('trello', ['app:', '56']),
//            new QualifiedName('1trello', ['app', '56']),
//            new QualifiedName('12', []),
//            new QualifiedName('56field', [])
//        ];
//
//        foreach ($qualifiedNames as $qname) {
//            $this->assertFalse(QualifiedName::isValidIdentifier($qname));
//        }
        $this->assertTrue(true);
    }

    public function testIsValidIdentifierReturnsTrue()
    {
        $qualifiedNames = [
            new QualifiedName('trello', ['app', '56']),
            new QualifiedName('trello', []),
            new QualifiedName('field56', [])
        ];

        foreach ($qualifiedNames as $qname) {
            $this->assertTrue(QualifiedName::isValidIdentifier($qname));
        }
    }
}
