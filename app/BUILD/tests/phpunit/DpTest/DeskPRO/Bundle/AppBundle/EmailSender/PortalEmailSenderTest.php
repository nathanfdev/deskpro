<?php

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

//        $person = $this->getPersonFactory()->createNewInvalidUser('john@appleseed.com', 'John Appleseed');
//        $this->getEmailSender()->sendWelcomeEmail($person);

//        $sources = $this->getSendmailSources($person->getPrimaryEmailAddress());
//        $last_source = end($sources);

//        $this->assertNotFalse($last_source, 'an email was sent');
//        $this->assertEquals('Thank you for registering', $last_source->getHeaderSubject(), 'subject is correct');
//        $this->assertContains('john@appleseed.com', $last_source->getHeaderTo(), 'sent to the correct address');

//        $this->assertContains(
//            '/validate/email/' . $person->getPrimaryEmail()->getId(),
//            $this->getMessageFromEmailSource($last_source),
//            'it contains the right link'
//        );
    }

    public function testCustomWelcomeEmail()
    {
        $this->installDataSet('fresh', true);

        $custom_template = <<<'CODE'
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
