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
namespace DpTest\DeskPRO\Bundle\AppBundle\EmailSender;

use Application\DeskPRO\Entity\Person;
use DpTest\PortalTestCase;
use DpTestSrc\TestBundle\Factory\PersonTestFactory;

class PortalEmailSenderTest extends PortalTestCase
{
    public function testSendWelcomeEmail()
    {
        //        $this->installDataSet('fresh', true);
//
//        $person = $this->getPersonFactory()->createNewInvalidUser('john@appleseed.com', 'John Appleseed');
//        $this->getEmailSender()->sendWelcomeEmail($person);
//
//        $sources = $this->getSendmailSources($person->getPrimaryEmailAddress());
//        $last_source = end($sources);
//
//        $this->assertNotFalse($last_source, 'an email was sent');
//        $this->assertEquals('Thank you for registering', $last_source->getHeaderSubject(), 'subject is correct');
//        $this->assertContains('john@appleseed.com', $last_source->getHeaderTo(), 'sent to the correct address');
//
//        $this->assertContains(
//            '/validate/email/' . $person->getPrimaryEmail()->getId(),
//            $this->getMessageFromEmailSource($last_source),
//            'it contains the right link'
//        );
    }

    public function testCustomWelcomeEmail()
    {
        $this->installDataSet('fresh', true);

        $custom_template = <<<CODE
<dp:subject>This is the custom subject</dp:subject>
Hello there this is the message
CODE;
        $this->saveCustomEmailTemplate('DeskPRO:emails_user:register-welcome.html.twig', $custom_template);

        $person = $this->getPersonFactory()->createNewInvalidUser('john@appleseed.com', 'John Appleseed');
        $this->getEmailSender()->sendWelcomeEmail($person);

        $this->assertEmailWithSubjectWasSentTo(
            $person->getPrimaryEmailAddress(),
            'This is the custom subject',
            'Hello there this is the message'
        );
    }

    /**
     * @return PersonTestFactory
     */
    public function getPersonFactory()
    {
        return $this->get('test_factory.person');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender
     */
    protected function getEmailSender()
    {
        return $this->get('portal_email_sender');
    }
}
