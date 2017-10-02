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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\EmailGateway\Reader\Item\AuthenticationResults;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use Orb\Util\Strings;

/**
 * The ezcParser uses the ezcMailParser class from ezComponents
 * to parse emails.
 *
 * @see http://ezcomponents.org/docs/api/trunk/introduction_Mail.html#mail-retrieval-and-parsing
 */
class EzcReader extends AbstractReader
{
    /**
     * @var EmailAccount
     */
    protected $emailAccount;

    /**
     * @var EmailAccountManager
     */
    protected $emailAccountManager;

    /**
     * @var AppEnv
     */
    protected $environment;

    /**
     * @var DeskproBlobStorage
     */
    protected $blobStorage;
    /**
     * @var \ezcMailParser|null
     */
    protected $parser = null;

    /**
     * @var \ezcMail
     */
    protected $mail = null;

    const DECRYPT_NO_ACCOUNT         = 'no_account';
    const DECRYPT_NO_KEY_FOR_ACCOUNT = 'no_key';
    const DECRYPT_FAILURE            = 'other_failure';
    /**
     * EzcReader constructor.
     *
     * @param EmailAccountManager $emailAccountManager
     * @param DeskproBlobStorage  $blobStorage
     * @param AppEnv              $environment
     */
    public function __construct(EmailAccountManager $emailAccountManager, DeskproBlobStorage $blobStorage, AppEnv $environment)
    {
        $this->emailAccountManager = $emailAccountManager;
        $this->blobStorage         = $blobStorage;
        $this->environment         = $environment;

        $opt = new \ezcMailParserOptions();

        $this->parser = new \ezcMailParser($opt);
        \ezcMailParser::setTmpDir(dp_get_tmp_dir().DIRECTORY_SEPARATOR);

        // Dont have ezc try and convert charsets, we'll handle that ourselves tyvm
        static $hasSetConvert = false;
        if (!$hasSetConvert) {
            $hasSetConvert = true;
            \ezcMailCharsetConverter::setConvertMethod(function ($text, $fromCharset) {
                return $text;
            });
        }
    }

    /**
     * @return EmailAccount
     */
    public function getEmailAccount()
    {
        return $this->emailAccount;
    }

    /**
     * @param EmailAccount $emailAccount
     *
     * @return EzcReader
     */
    public function setEmailAccount($emailAccount)
    {
        $this->emailAccount = $emailAccount;

        return $this;
    }

    /**
     * @return EmailAccountManager
     */
    public function getEmailAccountManager()
    {
        return $this->emailAccountManager;
    }

    /**
     * @return AppEnv
     */
    public function getEnv()
    {
        return $this->environment;
    }

    public function _kill()
    {
        parent::_kill();

        $this->parser = null;
        $this->mail   = null;
    }

    /**
     * @param $source
     */
    protected function _setRawSource($source)
    {
        $set        = new \ezcMailVariableSet($source);
        $this->mail = $this->parser->parseMail($set);

        if (!$this->mail || !isset($this->mail[0])) {
            throw new \InvalidArgumentException('Bad mail source, could not decode');
        }

        $this->mail = $this->mail[0];

        foreach ($this->mail->fetchParts() as $part) {
            if (isset($part->mimeType) && $part->mimeType === 'pkcs7-mime') {
                $this->decryptEmail();
            }
            if (isset($part->mimeType) && $part->mimeType === 'pkcs7-signature') {
                $this->validateSignature();
            }
        }
    }

    /**
     * @param $name
     *
     * @return Item\Header
     */
    protected function _getHeader($name)
    {
        $name         = strtolower($name);
        $header       = new Item\Header();
        $header->name = $name;

        $parts = (array) $this->mail->getHeader($name, true);
        if ($parts) {
            foreach ($parts as $p) {
                $header->header_parts[] = \ezcMailTools::mimeDecode($p, 'utf-8');
            }
        }

        return $header;
    }

    /**
     * @return AuthenticationResults[]
     */
    protected function _getAuthenticationResults()
    {
        $headers = $this->mail->getHeader('Authentication-Results', true);

        $authenticationResults = [];
        if ($headers) {
            foreach ($headers as $header) {
                $authenticationResult                                          = AuthenticationResults::parseHeader($header);
                $authenticationResults[$authenticationResult->getAuthservId()] = $authenticationResult;
            }
        } else {
            $receivedSpf = $this->mail->getHeader('Received-SPF', true);
            if ($receivedSpf) {
                foreach ($receivedSpf as $value) {
                    $authenticationResult                                          = AuthenticationResults::parseReceivedSpf($value);
                    $authenticationResults[$authenticationResult->getAuthservId()] = $authenticationResult;
                }
            }
        }

        return $authenticationResults;
    }

    /**
     * @return array
     */
    protected function _getCcAddresses()
    {
        $emails = [];

        foreach ($this->mail->cc as $cc) {
            $charset = $cc->charset;
            if (!$charset) {
                $charset = 'us-ascii';
            }

            $email                   = new Item\EmailAddress();
            $email->name             = $cc->name;
            $email->name_utf8        = Strings::convertToUtf8($cc->name, $charset);
            $email->email            = $cc->email;
            $email->original_charset = $charset;

            $emails[] = $email;
        }

        return $emails;
    }

    /**
     * @return array
     */
    protected function _getToAddresses()
    {
        $emails = [];

        foreach ($this->mail->to as $to) {
            $charset = $to->charset;
            if (!$charset) {
                $charset = 'us-ascii';
            }

            $email                   = new Item\EmailAddress();
            $email->name             = $to->name;
            $email->name_utf8        = Strings::convertToUtf8($to->name, $charset);
            $email->email            = $to->email;
            $email->original_charset = $charset;

            $emails[] = $email;
        }

        return $emails;
    }

    /**
     * @return Item\EmailAddress
     */
    protected function _getFromAddress()
    {
        if (!$this->mail->from || !$this->mail->from->email) {
            $email            = new Item\EmailAddress();
            $email->name      = '';
            $email->name_utf8 = '';
            $email->email     = '';

            return $email;
        }

        $charset = $this->mail->from->charset;
        if (!$charset) {
            $charset = 'us-ascii';
        }

        $email            = new Item\EmailAddress();
        $email->name      = $this->mail->from->name;
        $email->name_utf8 = Strings::convertToUtf8($this->mail->from->name, $charset);
        $email->email     = $this->mail->from->email;

        return $email;
    }

    /**
     * @return Item\EmailAddress|bool
     */
    protected function _getReplyToAddress()
    {
        $val = $this->mail->getHeader('Reply-To', false);
        if (!$val) {
            return false;
        }

        $addrs = \ezcMailTools::parseEmailAddresses($val);
        if (!$addrs) {
            return false;
        }

        $addr = array_shift($addrs);

        //(sic!) cuase I'm not sure about returnPath (it's set by SMTP server iirc)
        $charset = !empty($this->mail->from->charset) ? $this->mail->from->charset : $this->mail->from->charset;
        if (!$charset) {
            $charset = 'us-ascii';
        }

        $email            = new Item\EmailAddress();
        $email->name      = $addr->name ?: '';
        $email->name_utf8 = Strings::convertToUtf8($email->name, $charset);
        $email->email     = $addr->email;

        return $email;
    }

    /**
     * @return Item\EmailAddress|bool
     */
    protected function _getOriginalFromAddress()
    {
        $val = $this->mail->getHeader('X-Original-From', false);
        if (!$val) {
            return false;
        }

        $addrs = \ezcMailTools::parseEmailAddresses($val);
        if (!$addrs) {
            return false;
        }

        $addr = array_shift($addrs);

        $charset = $this->mail->from->charset;
        if (!$charset) {
            $charset = 'us-ascii';
        }

        $email            = new Item\EmailAddress();
        $email->name      = $addr->name ?: '';
        $email->name_utf8 = Strings::convertToUtf8($email->name, $charset);
        $email->email     = $addr->email;

        return $email;
    }

    /**
     * @return Item\Subject
     */
    protected function _getSubject()
    {
        if (!$this->mail->subject) {
            $subject                   = new Item\Subject();
            $subject->subject          = '';
            $subject->subject_utf8     = '';
            $subject->original_charset = 'UTF-8';

            return $subject;
        }

        $charset = $this->mail->subjectCharset;
        if (!$charset) {
            $charset = 'us-ascii';
        }

        $subject               = new Item\Subject();
        $subject->subject      = $this->mail->subject;
        $subject->subject_utf8 = Strings::convertToUtf8($this->mail->subject, $charset);

        return $subject;
    }

    /**
     * @return Item\Subject|null
     */
    protected function _getOriginalSubject()
    {
        $header = $this->getHeader('Thread-Topic');
        if (!$header || empty($header->header_parts)) {
            return null;
        }

        $subject                   = new Item\Subject();
        $subject->subject          = $header->getHeader();
        $subject->subject_utf8     = $subject->subject;
        $subject->original_charset = 'UTF-8';

        return $subject;
    }

    /**
     * @return array
     */
    protected function _getAttachments()
    {
        $attachments = [];

        $mail = $this->decryptedMail ? $this->decryptedMail : $this->mail;

        foreach ($mail->fetchParts() as $part) {
            if (
                $part instanceof \ezcMailFile
                || ($part->contentDisposition && $part->contentDisposition->disposition == 'attachment')
                || ($part instanceof \ezcMailText && $part->subType == 'calendar')
                || ($part instanceof \ezcMailRfc822Digest)
            ) {
                // We already analysed the signature or the encrypted content so we don't had it as an attachment
                if (isset($part->mimeType) && ($part->mimeType === 'pkcs7-signature' || $part->mimeType === 'pkcs7-mime')) {
                    continue;
                }
                $attach = new Item\Attachment();

                if ($part instanceof \ezcMailText) {
                    $attach->tmp_file = tempnam(dp_get_tmp_dir(), 'dpm');
                    file_put_contents($attach->tmp_file, $part->text);

                    if (isset($part->contentDisposition) && isset($part->contentDisposition->displayFileName)) {
                        try {
                            $attach->file_name = basename($part->contentDisposition->displayFileName);
                            $attach->mime_type = \Orb\Data\ContentTypes::getContentTypeFromFilename($part->contentDisposition->displayFileName);
                        } catch (\Exception $e) {
                        }
                    }

                    if (!$attach->file_name) {
                        if ($part instanceof \ezcMailText && $part->subType == 'calendar') {
                            $attach->file_name = 'icalendar.ics';
                            $attach->mime_type = 'text/calendar';
                        } else {
                            $attach->file_name = 'file.txt';
                            $attach->mime_type = 'text/plain';
                        }
                    }

                    if (!$attach->mime_type) {
                        $attach->mime_type = 'application/octet-stream';
                    }
                } elseif ($part instanceof \ezcMailRfc822Digest) {
                    // - We have hacked ezc to keep track of the raw mail source
                    // so we can just use that
                    if (!empty($part->dp_raw_source)) {
                        $attach->tmp_file = tempnam(dp_get_tmp_dir(), 'eml');
                        file_put_contents($attach->tmp_file, $part->dp_raw_source);

                        // If for some reason we dont have the raw source, this is the original way to read the mail
                        // based on the parsed source. I dont think this sholud ever happen though.
                    } else {
                        // ezc does charset conversion that makes the charset think its utf8
                        // but it may not be. we need to copy the original
                        if (isset($part->mail->body->originalCharset)) {
                            $bodyCharset              = $part->mail->body->originalCharset;
                            $attach->original_charset = $bodyCharset;
                        }

                        $attach->tmp_file = tempnam(dp_get_tmp_dir(), 'eml');
                        file_put_contents($attach->tmp_file, $part->generateBody());
                    }

                    if (isset($part->mail->headers) && !empty($part->mail->headers['subject'])) {
                        $filename          = $part->mail->headers['subject'];
                        $filename          = Strings::utf8_accents_to_ascii($filename);
                        $filename          = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $filename);
                        $filename          = preg_replace('#\-{2,}#', '-', $filename);
                        $filename          = trim($filename);
                        $filename          = trim($filename, '-');
                        $attach->file_name = $filename.'.eml';
                    }
                    if (!$attach->file_name) {
                        $attach->file_name = 'email.eml';
                    }
                    $attach->mime_type = 'message/rfc822';
                } elseif ($part->mimeType == 'ms-tnef' || $part->mimeType == 'application/ms-tnef') {
                    $attach        = null;
                    $winmailAttach = $this->decodeTnef($part);
                    foreach ($winmailAttach as $a) {
                        $attachments[] = $a;
                    }
                } else {
                    $attach->tmp_file = $part->fileName;

                    if (isset($part->contentDisposition)) {
                        foreach (['displayFileName', 'fileName'] as $field) {
                            if (!empty($part->contentDisposition->$field)) {
                                try {
                                    $attach->file_name = basename($part->contentDisposition->$field);
                                    $attach->mime_type = \Orb\Data\ContentTypes::getContentTypeFromFilename($part->contentDisposition->displayFileName);
                                } catch (\Exception $e) {
                                }
                            }

                            if ($attach->file_name && $attach->file_name !== 'filename') {
                                break;
                            }
                        }
                    } elseif (!empty($part->fileName)) {
                        try {
                            $attach->file_name = basename($part->fileName);
                            $attach->mime_type = \Orb\Data\ContentTypes::getContentTypeFromFilename($part->fileName);
                        } catch (\Exception $e) {
                        }
                    }

                    if (!$attach->file_name) {
                        $attach->file_name = 'file.txt';
                        $attach->mime_type = 'plain/text';
                    }

                    if (!$attach->mime_type) {
                        if (isset($part->contentType) && isset($part->mimeType) && $part->contentType && $part->mimeType) {
                            $attach->mime_type = "{$part->contentType}/{$part->mimeType}";
                        } else {
                            $attach->mime_type = 'application/octet-stream';
                        }
                    }
                }

                if ($attach) {
                    $attach->content_id = $part->getHeader('Content-ID');
                    if ($attach->content_id) {
                        // Content-ID is enclosed in brackets, remove those
                        $attach->content_id = preg_replace('#^<(.*?)>$#', '$1', $attach->content_id);
                    }

                    $attachments[] = $attach;
                }
            }
        }

        return $attachments;
    }

    /**
     * @return Item\BodyHtml
     */
    protected function _getBodyHtml()
    {
        $rawParts = [];

        $mail = $this->decryptedMail ? $this->decryptedMail : $this->mail;

        foreach ($mail->fetchParts(['ezcMailText']) as $part) {
            if (
                $part->subType == 'html'
                && !($part->contentDisposition && $part->contentDisposition->disposition == 'attachment')
            ) {
                $originalCharset = $this->getOriginalCharset($part);

                $body                   = new Item\BodyHtml();
                $body->body             = Strings::standardEol($part->text);
                $body->body_utf8        = Strings::convertToUtf8(Strings::standardEol($part->text), $originalCharset);
                $body->original_charset = $part->originalCharset;

                $rawParts[] = $body;
            }
        }

        //we're going append technical detail to html body if it exists.
        if ($rawParts) {
            foreach ($mail->fetchParts(['ezcMailDeliveryStatus']) as $part) {
                /* @var \ezcMailDeliveryStatus $part */
                $generatedBody   = Strings::standardEol($part->generateBody());
                $body            = new Item\BodyHtml();
                $body->body      = $generatedBody;
                $body->body_utf8 = $generatedBody;

                $rawParts[] = $body;
            }
        }

        if ($rawParts) {
            $allSame = true;
            $charset = null;

            $allUtf = '';
            $allRaw = '';

            foreach ($rawParts as $p) {
                $allUtf .= $p->body_utf8;
                $allRaw .= $p->body;

                if (!$charset) {
                    $charset = $p->original_charset;
                } elseif ($charset != $p->original_charset) {
                    $allSame = false;
                }
            }

            // All charsets were the same,
            // so we can have an accurate computed body with
            // accurate original_charset
            if ($allSame) {
                $body                   = new Item\BodyHtml();
                $body->body             = $allRaw;
                $body->body_utf8        = $allUtf;
                $body->original_charset = $charset;

                // Charsets differ, so we need
                // to construct based on the utf8-only body
            } else {
                $body                   = new Item\BodyHtml();
                $body->body             = $allUtf;
                $body->body_utf8        = $allUtf;
                $body->original_charset = 'UTF-8';
            }

            $body->raw_parts = $rawParts;

            return $body;
        } else {
            // Default to a blank body
            $body                   = new Item\BodyHtml();
            $body->body             = '';
            $body->body_utf8        = '';
            $body->original_charset = 'UTF-8';
            $body->raw_parts        = [clone $body];

            return $body;
        }
    }

    /**
     * @return Item\BodyText
     */
    protected function _getBodyText()
    {
        $rawParts = [];

        $mail = $this->decryptedMail ? $this->decryptedMail : $this->mail;

        foreach ($mail->fetchParts(['ezcMailText']) as $part) {
            if ($part->subType == 'plain') {
                $originalCharset = $this->getOriginalCharset($part);

                $body                   = new Item\BodyText();
                $body->body             = $part->text;
                $body->body_utf8        = Strings::convertToUtf8($part->text, $originalCharset);
                $body->original_charset = $originalCharset;

                $rawParts[] = $body;
            }
        }

        foreach ($mail->fetchParts(['ezcMailDeliveryStatus']) as $part) {
            /* @var \ezcMailDeliveryStatus $part */
            $generatedBody   = Strings::standardEol($part->generateBody());
            $body            = new Item\BodyHtml();
            $body->body      = $generatedBody;
            $body->body_utf8 = $generatedBody;

            $rawParts[] = $body;
        }

        if ($rawParts) {
            $allSame = true;
            $charset = null;

            $allUtf = '';
            $allRaw = '';

            foreach ($rawParts as $p) {
                $allUtf .= $p->body_utf8;
                $allRaw .= $p->body;

                if (!$charset) {
                    $charset = $p->original_charset;
                } elseif ($charset != $p->original_charset) {
                    $allSame = false;
                }
            }

            // All charsets were the same,
            // so we can have an accurate computed body with
            // accurate original_charset
            if ($allSame) {
                $body                   = new Item\BodyText();
                $body->body             = $allRaw;
                $body->body_utf8        = $allUtf;
                $body->original_charset = $charset;

                // Charsets differ, so we need
                // to construct based on the utf8-only body
            } else {
                $body                   = new Item\BodyText();
                $body->body             = $allUtf;
                $body->body_utf8        = $allUtf;
                $body->original_charset = 'UTF-8';
            }

            $body->raw_parts = $rawParts;

            return $body;
        } else {
            // Default to a blank body
            $body                   = new Item\BodyText();
            $body->body             = '';
            $body->body_utf8        = '';
            $body->original_charset = 'UTF-8';
            $body->raw_parts        = [clone $body];

            return $body;
        }
    }

    /**
     * @param \ezcMailPart $part
     *
     * @return mixed
     */
    protected function getOriginalCharset(\ezcMailPart $part)
    {
        $originalCharset = $part->originalCharset;
        if (!$originalCharset) {
            $originalCharset = 'us-ascii';
        }

        if ($this->hasProperty('override_from_charset')) {
            $originalCharset = $this->getProperty('override_from_charset');
        }

        return $originalCharset;
    }

    /**
     * Decode a Microsoft Outlook TNEF part (winmail.dat).
     *
     * @param $part \ezcMailPart part to decode
     *
     * @return array
     */
    public function decodeTnef($part)
    {
        $attachments = [];

        $tnef = new \tnef();

        $tnefArr = $tnef->decompress(file_get_contents($part->fileName));
        if (!$tnefArr || !is_array($tnefArr)) {
            return [];
        }

        foreach ($tnefArr as $pid => $winatt) {
            $attach = new Item\Attachment();

            // We didnt decode it or its a bad file
            if (empty($winatt['name']) || empty($winatt['type']) || empty($winatt['subtype']) || empty($winatt['stream'])) {
                continue;
            }

            $attach->file_name       = trim($winatt['name']);
            $attach->ctype_primary   = trim(strtolower($winatt['type']));
            $attach->ctype_secondary = trim(strtolower($winatt['subtype']));
            $attach->mime_type       = $attach->ctype_primary.'/'.$attach->ctype_secondary;
            $attach->file_contents   = $winatt['stream'];
            $attach->size            = strlen($attach->file_contents);

            $attachments[] = $attach;

            unset($tnefArr[$pid]);
        }

        return $attachments;
    }

    public function decryptEmail()
    {
        $account = $this->findEmailAccountFrom();

        if (!$account) {
            $this->decryptionError = self::DECRYPT_NO_ACCOUNT;
        } else {
            $certBlob = $account->getCertBlob();
            $keyBlob  = $account->getKeyBlob();
            if ($certBlob && $keyBlob) {
                $public    = $this->blobStorage->copyBlobRecordToString($certBlob);
                $private   = $this->blobStorage->copyBlobRecordToString($keyBlob);
                $fileId    = uniqid('encMails', true);
                $tmpDir    = $this->getEnv()->getUserTmpDir();
                $encrypted = $tmpDir.'/'.$fileId.'encrypted.txt';
                file_put_contents($encrypted, $this->raw_source);
                $outfile = $tmpDir.'/'.$fileId.'decrypted.txt';
                try {
                    $key = $account->getKeyPassPhrase() ?
                        [$private, $account->getKeyPassPhrase()] :
                        $private;
                    if (openssl_pkcs7_decrypt($encrypted, $outfile, $public, $key)) {
                        $set                 = new \ezcMailVariableSet(file_get_contents($outfile));
                        $this->decryptedMail = $this->parser->parseMail($set);

                        if (!$this->decryptedMail || !isset($this->decryptedMail[0])) {
                            throw new \InvalidArgumentException('Bad mail source, could not decode');
                        }

                        $this->decryptedMail = $this->decryptedMail[0];

                        foreach ($this->decryptedMail->fetchParts() as $part) {
                            if (isset($part->mimeType) && $part->mimeType === 'pkcs7-signature') {
                                $this->validateSignature($outfile);
                            }
                        }
                    } else {
                        $this->decryptionError = self::DECRYPT_FAILURE;
                    }
                } finally {
                    @unlink($encrypted);
                    @unlink($outfile);
                }
            } else {
                $this->decryptionError = self::DECRYPT_NO_KEY_FOR_ACCOUNT;
            }
        }
    }

    public function validateSignature($file = null)
    {
        if (!$file) {
            $tmpDir = $this->getEnv()->getUserTmpDir();
            $fileId = uniqid('sigMails', true);
            $file   = $tmpDir.'/'.$fileId.'encrypted.txt';
            file_put_contents($file, $this->raw_source);
        }
        try {
            $this->isSigned = openssl_pkcs7_verify($file, 0);
        } finally {
            @unlink($file);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\EmailAccount|null
     */
    private function findEmailAccountFrom()
    {
        if ($this->emailAccount) {
            return $this->emailAccount;
        }
        foreach ($this->getReceivedAddresses() as $email) {
            $account = $this->emailAccountManager->findAccountForEmailAddress($email->email, 'is_enabled');
            if ($account) {
                return $account;
            }
        }

        return null;
    }

    /**
     * @return Blob
     */
    public function getSourceAsBlob()
    {
        $filename = 'original.eml';
        $mimeType = 'message/rfc822';

        return $this->blobStorage->createBlobRecordFromString(
            $this->raw_source,
            $filename,
            $mimeType,
            ['is_temp' => false]
        );
    }
}
