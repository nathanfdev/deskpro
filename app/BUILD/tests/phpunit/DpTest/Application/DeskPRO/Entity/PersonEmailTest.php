<?php

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
