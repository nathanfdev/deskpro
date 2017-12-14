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

namespace DpBehat\Portal;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\EmailGateway\Reader\EzcReader;
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
        /** @var SendmailSourceRepository $ssRepo */
        $ssRepo = $this->repository(SendmailSource::class);

        return $ssRepo->getLatest();
    }

    /**
     * An array of data that comes from the last email saved to "sendmail_sources".
     *
     * @return array
     */
    protected function getLastEmailData()
    {
        $lastEmail = $this->getLastEmail();

        $blob = $lastEmail->getBlob();

        /** @var DeskproBlobStorage $blobStorage */
        $blobStorage = $this->get('deskpro.blob_storage');

        $emailBody    = $blobStorage->copyBlobRecordToString($blob);
        $emailSubject = $lastEmail->getHeaderSubject();

        $readerFactory = $this->get('email.ezc_reader_factory');
        /** @var EzcReader $reader */
        $reader = $readerFactory->create();
        $reader->setRawSource($emailBody);
        $emailBody = $reader->getBodyHtml()->getBodyUtf8();

        var_dump($emailBody);

        return [
            'body'    => $emailBody,
            'subject' => $emailSubject,
        ];
    }

    protected function getBodyOfLastEmail()
    {
        $emailData = $this->getLastEmailData();

        return $emailData['body'];
    }

    protected function getSubjectOfLastEmail()
    {
        $emailData = $this->getLastEmailData();

        return $emailData['subject'];
    }

    /**
     * @Then I should receive an email containing :text
     */
    public function iShouldReceiveAnEmailContaining($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->getBodyOfLastEmail());
    }

    /**
     * @Then I should receive an email containing the phrase :phrase
     */
    public function iShouldReceiveAnEmailContainingPhrase($phrase)
    {
        \PHPUnit_Framework_Assert::assertContains($this->get('language_manager')->phrase($phrase), $this->getBodyOfLastEmail());
    }

    /**
     * @Given A ticket message is sent
     */
    public function iSendATicketEmail()
    {
        $ticket    = DataContext::getReference('ticket');
        $message   = DataContext::getReference('ticket_message');
        $viewModel = $this->get('email.user_viewmodel_factory')
            ->createTicketReplyByAgentModel(
                $ticket,
                $message
            );
        $this->get('email.email_sender')
            ->send($viewModel,
                ['to' => 'foo@example.com']);
    }
}
