<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpBehat\Portal;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\EmailBundle\Entity\SendmailSource;
use Application\EmailBundle\EntityRepository\SendmailSourceRepository;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;
use DpBehat\KernelAwareTrait;

class EmailContext extends BaseContext implements KernelAwareContext
{
    use KernelAwareTrait;

    /**
     * @return SendmailSource
     */
    protected function getLastEmail()
    {
        /** @var SendmailSourceRepository $ss_repo */
        $ss_repo = $this->repository(SendmailSource::class);

        return $ss_repo->getLatest();
    }

    /**
     * An array of data that comes from the last email saved to "sendmail_sources".
     *
     * @return array
     */
    protected function getLastEmailData()
    {
        $last_email = $this->getLastEmail();

        $blob = $last_email->getBlob();

        /** @var DeskproBlobStorage $blob_storage */
        $blob_storage = $this->get('deskpro.blob_storage');

        $email_body    = $blob_storage->copyBlobRecordToString($blob);
        $email_subject = $last_email->getHeaderSubject();

        $reader = new \Application\DeskPRO\EmailGateway\Reader\EzcReader();
        $reader->setRawSource($email_body);
        $email_body = $reader->getBodyText()->getBodyUtf8();

        return [
            'body'    => $email_body,
            'subject' => $email_subject,
        ];
    }

    protected function getBodyOfLastEmail()
    {
        $email_data = $this->getLastEmailData();

        return $email_data['body'];
    }

    protected function getSubjectOfLastEmail()
    {
        $email_data = $this->getLastEmailData();

        return $email_data['subject'];
    }

    /**
     * @Then I should receive an email containing :text
     */
    public function iShouldReceiveAnEmailContaining($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->getBodyOfLastEmail());
    }

    /**
     * @Given A ticket message is sent
     */
    public function iSendATicketEmail()
    {
        $ticket    = DataContext::getReference('ticket');
        $message   = DataContext::getReference('ticket_message');
        $viewModel = $this->get('email.ticket_viewmodel_factory')
            ->createTicketReplyModel(
                $ticket,
                $message
            );
        $this->get('email.email_sender')
            ->send('ticket_reply_byagent.html',
                ['to' => 'foo@example.com'],
                $viewModel);
    }
}
