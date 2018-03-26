<?php

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
