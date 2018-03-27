<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Reader;

use Application\DeskPRO\EmailGateway\Reader\Item\AuthenticationResults;

class ValueReader extends AbstractReader
{
    /** @var array */
    private $values = [];

    /**
     * Sets values:.
     *
     * @option array headers  An array of k=>array(values). k sholud be lowercase.
     * @option array ccs      An array of email=>name
     * @option array tos      An array of email=>name
     * @option array from     email, or array(name, email)
     * @option string subject
     * @option string body_html
     * @option string body_text
     *
     * @param array $values
     */
    public function setValues(array $values)
    {
        $this->values = $values;
    }

    public function _kill()
    {
    }

    public function setRawSource($source)
    {
        throw new \RuntimeException();
    }

    public function getRawSource()
    {
        throw new \RuntimeException();
    }

    public function getRawHeaders()
    {
        throw new \RuntimeException();
    }

    protected function _setRawSource($x)
    {
        // Nothing
    }

    protected function _getHeader($name)
    {
        $name         = strtolower($name);
        $header       = new Item\Header();
        $header->name = $name;

        $parts = isset($this->values['headers'][$name]) ? $this->values['headers'][$name] : [];
        if ($parts) {
            foreach ($parts as $p) {
                $header->header_parts[] = $p;
            }
        }

        return $header;
    }

    /**
     * @return AuthenticationResults[]
     */
    protected function _getAuthenticationResults()
    {
        $headers = isset($this->values['headers']['Authentication-Results']) ? $this->values['headers']['Authentication-Results'] : [];

        $authenticationResults = [];
        if ($headers) {
            foreach ($headers as $header) {
                $authenticationResult                                          = AuthenticationResults::parseHeader($header);
                $authenticationResults[$authenticationResult->getAuthservId()] = $authenticationResult;
            }
        } else {
            $receivedSpf = isset($this->values['headers']['Received-SPF']) ? $this->values['headers']['Received-SPF'] : [];
            if ($receivedSpf) {
                foreach ($receivedSpf as $value) {
                    $authenticationResult                                          = AuthenticationResults::parseReceivedSpf($value);
                    $authenticationResults[$authenticationResult->getAuthservId()] = $authenticationResult;
                }
            }
        }

        return $authenticationResults;
    }

    protected function _getCcAddresses()
    {
        $emails = [];

        $valEmails = isset($this->values['ccs']) ? $this->values['ccs'] : [];

        foreach ($valEmails as $name => $eml) {
            $email                   = new Item\EmailAddress();
            $email->name             = $name;
            $email->name_utf8        = $name;
            $email->email            = $eml;
            $email->original_charset = 'UTF-8';

            $emails[] = $email;
        }

        return $emails;
    }

    protected function _getToAddresses()
    {
        $emails = [];

        $valEmails = isset($this->values['tos']) ? $this->values['tos'] : [];

        foreach ($valEmails as $name => $eml) {
            $email                   = new Item\EmailAddress();
            $email->name             = $name;
            $email->name_utf8        = $name;
            $email->email            = $eml;
            $email->original_charset = 'UTF-8';

            $emails[] = $email;
        }

        return $emails;
    }

    protected function _getFromAddress()
    {
        if (!isset($this->values['from'])) {
            $email            = new Item\EmailAddress();
            $email->name      = '';
            $email->name_utf8 = '';
            $email->email     = '';

            return $email;
        }

        if (is_array($this->values['from'])) {
            if (isset($this->values['from'][0])) {
                $name = $this->values['from'][0];
                $eml  = $this->values['from'][1];
            } else {
                $name = $this->values['from']['name'];
                $eml  = $this->values['from']['email'];
            }
        } else {
            $name = '';
            $eml  = $this->values['from'];
        }

        $email            = new Item\EmailAddress();
        $email->name      = $name;
        $email->name_utf8 = $name;
        $email->email     = $eml;

        return $email;
    }

    protected function _getReplyToAddress()
    {
        return false;
    }

    protected function _getOriginalFromAddress()
    {
        return false;
    }

    protected function _getSubject()
    {
        if (!isset($this->values['subject'])) {
            $subject                   = new Item\Subject();
            $subject->subject          = '';
            $subject->subject_utf8     = '';
            $subject->original_charset = 'UTF-8';

            return $subject;
        }

        $subject                   = new Item\Subject();
        $subject->subject          = $this->values['subject'];
        $subject->subject_utf8     = $this->values['subject'];
        $subject->original_charset = 'UTF-8';

        return $subject;
    }

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

    protected function _getAttachments()
    {
        //todo
        return [];
    }

    protected function _getBodyHtml()
    {
        $body                   = new Item\BodyHtml();
        $body->body             = isset($this->values['body_html']) ? $this->values['body_html'] : '';
        $body->body_utf8        = $body->body;
        $body->original_charset = 'UTF-8';

        return $body;
    }

    protected function _getBodyText()
    {
        $body                   = new Item\BodyHtml();
        $body->body             = isset($this->values['body_text']) ? $this->values['body_text'] : '';
        $body->body_utf8        = $body->body;
        $body->original_charset = 'UTF-8';

        return $body;
    }
}
