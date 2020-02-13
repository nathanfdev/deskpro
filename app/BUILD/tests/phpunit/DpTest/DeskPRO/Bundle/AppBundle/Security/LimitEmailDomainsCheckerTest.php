<?php

namespace DeskPRO\Bundle\ApiBundle\Security;

use DpTest\PortalTestCase;

class LimitEmailDomainsCheckerTest extends PortalTestCase
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Security\LimitEmailDomainsChecker
     */
    private $checker;

    public function setUp()
    {
        $this->checker = $this->getContainer()->get('dp_limit_email_domains_checker');
    }

    /**
     * @testWith ["user@example.com",  "example.com", true]
     *           ["user@example.com",  "*.example.com", false]
     *           ["user@sub.example.com",  "sub.example.com", true]
     *           ["user@example.com",  "sub.example.com", false]
     *           ["user@deep.sub.example.com",  "deep.sub.example.com", true]
     *           ["user@deep.sub.example.com",  "*.example.com", true]
     *           ["user@sub.example.com",  "another_sub.example.com", false]
     *
     * @param string $email
     * @param string $pattern
     * @param bool   $expectedResult
     */
    public function testValidateEmail($email, $pattern, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->checker->validateEmailDomain($pattern, $email));
    }
}
