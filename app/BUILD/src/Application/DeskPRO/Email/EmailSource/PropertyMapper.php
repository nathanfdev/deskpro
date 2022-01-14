<?php

namespace Application\DeskPRO\Email\EmailSource;

use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\EmailGateway\Reader\EzcReader;
use Application\DeskPRO\EmailGateway\Reader\EzcReaderFactory;
use Application\DeskPRO\Entity\EmailSource;
use Orb\Util\Strings;

class PropertyMapper
{
    /** @var EmailAccountManager */
    private $accountManager;

    /** @var EzcReaderFactory */
    private $readerFactory;

    public function __construct(EmailAccountManager $accountManager, EzcReaderFactory $readerFactory)
    {
        $this->accountManager = $accountManager;
        $this->readerFactory  = $readerFactory;
    }

    /**
     * @param $rawSource
     *
     * @return EzcReader
     */
    public function createReader($rawSource)
    {
        $normalized = Strings::standardEol($rawSource);
        $reader     = $this->readerFactory->create();
        $reader->setRawSource($normalized);

        return $reader;
    }

    /**
     * @param EzcReader $reader
     * @param EmailSource $source
     *
     * @return EmailSource
     */
    public function read(EzcReader $reader, EmailSource $source)
    {
        $rawSource = $reader->getRawSource();

        $this->mapHeaderProperties($rawSource, $source, $reader);
        $this->mapEmailProperties($reader, $source);

        return $source;
    }

    private function mapHeaderProperties($rawSource, EmailSource $source, EzcReader $reader)
    {
        $normalized = Strings::standardEol($rawSource);

        $headerEnd = strpos($normalized, "\n\n");
        if ($headerEnd === false) {
            // Means an empty body (eg message with only subject)
            // But we trimmed above so the \n\n sep would be trimmed off
            $normalized .= "\n\n";
            $headerEnd = strpos($normalized, "\n\n");
        }
        $rawHeaders = trim(substr($normalized, 0, $headerEnd));
        if (isset($rawHeaders[4000])) {
            $rawHeaders = substr($rawHeaders, 0, 4000);
        }
        $source->fromArray([
            'headers'        => $rawHeaders,
            'header_to'      => $reader->getHeader('To')->getAllParts()
                ? implode(',', $reader->getHeader('To')->getAllParts()) : '',
            'header_cc'      => $reader->getHeader('Cc')->getAllParts()
                ? implode(',', $reader->getHeader('Cc')->getAllParts()) : '',
            'header_from'    => $reader->getHeader('From')
                ? implode(',', $reader->getHeader('From')->getAllParts()) : '',
            'header_subject' => $reader->getHeader('Subject')
                ? implode(',', $reader->getHeader('Subject')->getAllParts()) : '',
        ]);
    }

    /**
     * @param EzcReader $reader
     * @param EmailSource $source
     */
    private function mapEmailProperties(EzcReader $reader, EmailSource $source)
    {
        $account = null;
        foreach ($reader->getReceivedAddresses() as $email) {
            $account = $this->accountManager->findAccountForEmailAddress($email->email, 'is_enabled');
            if ($account) {
                break;
            }
        }

        $fromEmail = $reader->getFromAddress();
        $source->fromArray([
            'email_account'  => $account,
            'from_email'     => $fromEmail ? $fromEmail->getEmail() : null,
        ]);
    }
}
