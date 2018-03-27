<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader;

use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\EmailGateway\Reader\Item\Header;
use Orb\Util\Strings;

abstract class AbstractReader
{
    /** @var array */
    protected $vals = [];
    /** @var array */
    protected $properties = [];
    /** @var string */
    protected $raw_source;
    /** @var string */
    protected $raw_headers;
    /** @var array */
    protected $from_headers = ['from'];

    /**
     * @var \ezcMail
     */
    protected $decryptedMail = null;

    /**
     * @var string
     */
    protected $decryptionError = null;

    /**
     * @var bool
     */
    protected $isSigned = null;

    public function _kill()
    {
        $this->vals        = null;
        $this->properties  = null;
        $this->raw_source  = null;
        $this->raw_headers = null;
    }

    public function setFromHeaderPriority(array $headers)
    {
        $this->from_headers = $headers;
    }

    public function resetAll()
    {
        $this->vals       = [];
        $this->properties = [];
    }

    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function getProperty($name, $default = null)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }

    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    public function setRawSource($source)
    {
        $this->raw_source = Strings::standardEol($source);

        $pos = strpos($this->raw_source, "\n\n");
        if ($pos) {
            $this->raw_headers = substr($this->raw_source, 0, $pos);
        }

        $this->_setRawSource($source);
    }

    public function getRawSource()
    {
        return $this->raw_source;
    }

    public function getRawHeaders()
    {
        return $this->raw_headers;
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\BodyText
     */
    public function getBodyText()
    {
        if (!isset($this->vals['body_text'])) {
            $this->vals['body_text'] = $this->_getBodyText();
        }

        return $this->vals['body_text'];
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\BodyHtml
     */
    public function getBodyHtml()
    {
        if (!isset($this->vals['body_html'])) {
            $this->vals['body_html'] = $this->_getBodyHtml();
        }

        return $this->vals['body_html'];
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\Attachment[]
     */
    public function getAttachments()
    {
        if (!isset($this->vals['attach'])) {
            $this->vals['attach'] = $this->_getAttachments();
        }

        return $this->vals['attach'];
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\Subject
     */
    public function getSubject()
    {
        if (!isset($this->vals['subject'])) {
            $this->vals['subject'] = $this->_getSubject();
        }

        return $this->vals['subject'];
    }

    /**
     * If the email contains a Thread-Topic that tells us the original subject, then that.
     *
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\Subject
     */
    public function getOriginalSubject()
    {
        if (!isset($this->vals['original_subject'])) {
            $this->vals['original_subject'] = $this->_getOriginalSubject();
        }

        return $this->vals['original_subject'];
    }

    /**
     * Gets the from address based on the configured 'from_headers' array.
     *
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress
     */
    public function getFromAddress()
    {
        foreach ($this->from_headers as $header) {
            switch ($header) {
                case 'from':
                    $val = $this->getRealFromAddress();
                    break;
                case 'reply-to':
                    $val = $this->getReplyToAddress();
                    break;
                case 'x-original-from':
                    $val = $this->getOriginalFromAddress();
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown from header: $header");
            }

            if ($val) {
                return $val;
            }
        }

        return;
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress
     */
    public function getRealFromAddress()
    {
        if (!isset($this->vals['from_address'])) {
            $this->vals['from_address'] = $this->_getFromAddress();
        }

        return $this->vals['from_address'];
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress|null
     */
    public function getReplyToAddress()
    {
        if (!isset($this->vals['reply_to_address'])) {
            $this->vals['reply_to_address'] = $this->_getReplyToAddress();
        }

        return $this->vals['reply_to_address'] ?: null;
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress|null
     */
    public function getOriginalFromAddress()
    {
        if (!isset($this->vals['original_from_address'])) {
            $this->vals['original_from_address'] = $this->_getOriginalFromAddress();
        }

        return $this->vals['original_from_address'] ?: null;
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
     */
    public function getToAddresses()
    {
        if (!isset($this->vals['to_address'])) {
            $this->vals['to_address'] = $this->_getToAddresses();
        }

        return $this->vals['to_address'];
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
     */
    public function getReceivedAddresses()
    {
        $to      = $this->getToAddresses();
        $cc      = $this->getCcAddresses();
        $orig_to = null;

        if ($orig_to_email = $this->getOriginalTo()) {
            $eml        = new EmailAddress();
            $eml->email = strtolower($orig_to_email);
            $orig_to    = [$eml];
        }

        $all = [];
        if ($to) {
            $all = array_merge($all, $to);
        }
        if ($cc) {
            $all = array_merge($all, $cc);
        }
        if ($orig_to) {
            $all = array_merge($all, $orig_to);
        }

        return $all;
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
     */
    public function getCcAddresses()
    {
        if (!isset($this->vals['cc_addresses'])) {
            $this->vals['cc_addresses'] = $this->_getCcAddresses();
        }

        return $this->vals['cc_addresses'];
    }

    /**
     * This is a collection of both To and CC addresses.
     *
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
     */
    public function getDeliveredAddresses()
    {
        $to   = $this->getToAddresses();
        $cc   = $this->getCcAddresses();
        $from = $this->getFromAddress();

        $all = array_merge($to, $cc);
        if ($from) {
            $all[] = $from;
        }

        return $all;
    }

    /**
     * @return string
     */
    public function getOriginalTo()
    {
        if (isset($this->vals['original_to'])) {
            return $this->vals['original_to'] ? $this->vals['original_to'] : null;
        }

        $try = new \Application\DeskPRO\Config\UserFileConfig('original-to-headers');
        $try = $try->all();

        foreach ($try as $headerName) {
            if (!($h = $this->getHeader($headerName))) {
                return;
            }

            if (!$h->getHeader() || !\Orb\Validator\StringEmail::isValueValid($h->getHeader())) {
                continue;
            }

            $this->vals['original_to'] = strtolower($h->getHeader());

            return strtolower($h->getHeader());
        }

        $this->vals['original_to'] = false;

        return;
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\Header
     */
    public function getHeader($header)
    {
        if (!isset($this->vals['headers']) || !isset($this->vals['headers'][$header])) {
            if (!isset($this->vals['headers'])) {
                $this->vals['headers'] = [];
            }
            $this->vals['headers'][$header] = $this->_getHeader($header);
        }

        return $this->vals['headers'][$header];
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\AuthenticationResults
     */
    public function getAuthenticationResults()
    {
        if (!isset($this->vals['authentication_results'])) {
            $this->vals['authentication_results'] = $this->_getAuthenticationResults();
        }

        return $this->vals['authentication_results'];
    }

    abstract protected function _setRawSource($source);
    abstract protected function _getBodyText();
    abstract protected function _getBodyHtml();
    abstract protected function _getAttachments();
    abstract protected function _getSubject();
    abstract protected function _getFromAddress();
    abstract protected function _getReplyToAddress();
    abstract protected function _getOriginalFromAddress();
    abstract protected function _getToAddresses();
    abstract protected function _getCcAddresses();
    abstract protected function _getHeader($header);
    abstract protected function _getAuthenticationResults();

    /**
     * Returns true if message marks itself as from a robot.
     *
     * @return bool
     */
    public function isFromRobot()
    {
        $deskproAuto = $this->getHeader('X-DeskPRO-Auto')->getAllParts();
        if ($deskproAuto) {
            foreach ($deskproAuto as $v) {
                if (stripos($v, 'Yes') !== false) {
                    return true;
                }
            }
        }

        $auto = $this->getHeader('Auto-Submitted')->getAllParts();
        if ($auto) {
            foreach ($auto as $v) {
                $v = strtolower($v);
                if (strpos($v, 'auto-replied') !== false || strpos($v, 'auto-notified') !== false || strpos($v, 'auto-generated') !== false) {
                    return true;
                }
            }
        }

        $auto = $this->getHeader('X-Autoreply')->getAllParts();
        if ($auto) {
            foreach ($auto as $v) {
                $v = strtolower($v);
                if ($v == '1' || $v == 'yes') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the email was sent via outlook.
     *
     * @return bool
     */
    public function isOutlookMailer()
    {
        if (isset($this->vals['is_outlook'])) {
            return $this->vals['is_outlook'];
        }

        $isOutlook = false;

        $mailer = $this->getHeader('X-Mailer');
        if ($mailer && strpos($mailer->getHeader(), 'Outlook') !== false) {
            $isOutlook = true;
        }
        if (!$isOutlook) {
            $headers = $this->getRawHeaders();
            if (preg_match('#^X\-MS\-#', $headers)) {
                $isOutlook = true;
            }
        }

        $this->vals['is_outlook'] = $isOutlook;

        return $this->vals['is_outlook'];
    }

    /**
     * Gets a Date object representing the Date header or null if there is no Date header.
     * If there are multiple Date headers, the latest (closest to now) date is used.
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        if (isset($this->vals['date'])) {
            return $this->vals['date'] ? $this->vals['date'] : null;
        }

        $this->vals['date'] = false;

        $useDate = null;
        $date    = null;

        $dateHeader = $this->getHeader('Date');
        if (!$dateHeader || !count($dateHeader->header_parts)) {
            return;
        }

        foreach ($dateHeader->header_parts as $date_part) {
            if (!is_string($date_part)) {
                continue;
            }

            $date = \DateTime::createFromFormat(\DateTime::RFC2822, $date_part);

            if ($date && (!$useDate || $date > $useDate)) {
                $useDate = $date;
            }
        }

        if ($useDate) {
            $this->vals['date'] = $useDate;

            return $useDate;
        }

        return;
    }

    /**
     * @return null|string Message-Id header
     */
    public function getId()
    {
        /* @var Header $id */
        if (!$id = $this->getHeader('message-id')) {
            return;
        }
        $id = reset($id->header_parts);

        if (20 > $length = strlen($id)) {
            return;
        }

        $matchLt = false;
        $matchAt = false;
        $newId   = '';

        for ($i = 0; $i < $length; ++$i) {
            $char = $id[$i];

            if ('<' === $char) {
                $matchLt = true;
                continue;
            }

            if ('>' === $char) {
                break;
            }

            if ('@' === $char) {
                if (!$matchLt) {
                    break;
                }
                $matchAt = true;
            }

            if ($matchLt && '"' !== $char) {
                $newId .= $char;
            }
        }

        if (!$matchLt || !$matchAt) {
            return;
        }

        return $newId;
    }

    /**
     * @return bool
     */
    public function isSigned()
    {
        return $this->isSigned;
    }

    /**
     * @return \ezcMail
     */
    public function getDecryptedMail()
    {
        return $this->decryptedMail;
    }

    /**
     * @return string
     */
    public function getDecryptionError()
    {
        return $this->decryptionError;
    }
}
