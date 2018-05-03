<?php

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
