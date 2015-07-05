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
 */

namespace DpTest\DeskPRO\Bundle\Appbundle\EmailSender;

use Application\DeskPRO\Entity\Person;
use DpTest\PortalTestCase;
use DpTests\TestBundle\Factory\PersonTestFactory;

class PortalEmailSenderTest extends PortalTestCase
{
    public function testSendWelcomeEmail()
    {
        $this->installDataSet('fresh', true);

        $person = $this->getPersonFactory()->createNewInvalidUser('john@appleseed.com', 'John Appleseed');
        $this->getEmailSender()->sendWelcomeEmail($person);

        $sources = $this->getSendmailSources($person->getPrimaryEmailAddress());
        $last_source = end($sources);

        $this->assertNotFalse($last_source, 'an email was sent');
        $this->assertEquals('Thank you for registering', $last_source->getHeaderSubject(), 'subject is correct');
        $this->assertContains('john@appleseed.com', $last_source->getHeaderTo(), 'sent to the correct address');
    }

    public function testCustomWelcomeEmail()
    {
        // test creating a custom template for 'DeskPRO:emails_user:register-welcome.html.twig' in DB
        // then, we're moving this to EmailBundle:Email:register-welcome.html.twig
        $this->markTestIncomplete('awaiting implementation');
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

    protected function getSendmailSources($email)
    {
        /** @var \Application\EmailBundle\EntityRepository\SendmailSourceRepository $repo */
        $repo = $this->getRepo('EmailBundle:SendmailSource');

        $query = $repo->createQueryBuilder('s')
            ->select('s')
            ->where("s.to_emails LIKE '%$email%'")
            ->orderBy('s.date_created', 'DESC')
        ;

        return $query->getQuery()->getResult();
    }
}
