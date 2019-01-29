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
        $this->readerFactory = $readerFactory;
    }

    /**
     * @param $rawSource
     * @return EzcReader
     */
    function createReader($rawSource)
    {
        $normalized = Strings::standardEol($rawSource);
        $reader = $this->readerFactory->create();
        $reader->setRawSource($normalized);

        return $reader;
    }

    /**
     * @param EzcReader $reader
     * @param EmailSource $source
     * @return EmailSource
     */
    function read( EzcReader $reader, EmailSource $source)
    {
        $rawSource = $reader->getRawSource();

        $this->mapHeaderProperties($rawSource, $source);
        $this->mapEmailProperties($reader, $source);

        return $source;
    }

    private function mapHeaderProperties($rawSource, EmailSource $source)
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
            'header_to'      => Strings::extractRegexMatch('#^To:\s*(.*?)$#m', $rawHeaders) ?: '',
            'header_cc'      => Strings::extractRegexMatch('#^Cc:\s*(.*?)$#m', $rawHeaders) ?: '',
            'header_from'    => Strings::extractRegexMatch('#^From:\s*(.*?)$#m', $rawHeaders) ?: '',
            'header_subject' => Strings::extractRegexMatch('#^Subject:\s*(.*?)$#m', $rawHeaders) ?: '',
        ]);
    }

    /**
     * @param EzcReader $reader
     * @param EmailSource $source
     */
    private function mapEmailProperties( EzcReader $reader, EmailSource $source)
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
            'from_email'     => $fromEmail ? $fromEmail->getEmail() : null
        ]);

    }
}
