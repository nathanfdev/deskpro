<?php

namespace DpTest\DeskPRO\Application\Entity;

use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\PersonEmail;
use DpTest\PortalTestCase;

/**
 * Class PersonEmailTest.
 */
class PersonEmailTest extends PortalTestCase
{
    public function setUp()
    {
        $this->installDataSet('fresh', true);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Email address is empty
     */
    public function testEmptyEmailValidation()
    {
        $email = new PersonEmail();

        $em = $this->getEntityManager();
        $em->persist($email);
        $em->flush();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage is an a gateway account address
     */
    public function testEmailAccountValidation()
    {
        $em = $this->getEntityManager();

        $emailAccount = new EmailAccount();
        $emailAccount->setAccountType('outgoing');
        $emailAccount->setAddress('email@example.com');

        $em->persist($emailAccount);
        $em->flush();

        $email = new PersonEmail();
        $email->setEmail('email@example.com');

        $em->persist($email);
        $em->flush();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage is not valid email address
     * @dataProvider emailFormatValidationProvider
     *
     * @param mixed $emailAddress
     */
    public function testEmailFormatValidation($emailAddress)
    {
        $email = new PersonEmail();
        $email->setEmail($emailAddress);

        $em = $this->getEntityManager();
        $em->persist($email);
        $em->flush();
    }

    /**
     * @return array
     */
    public function emailFormatValidationProvider()
    {
        return [
            [1],
            ['some_string'],
            ['email.domain'],
        ];
    }
}
