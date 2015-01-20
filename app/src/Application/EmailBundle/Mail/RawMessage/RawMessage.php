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
 * @subpackage
 */

namespace Application\EmailBundle\Mail\RawMessage;

class RawMessage
{
    /**
     * array('email' => email, 'name' => 'name')
     * @var array
     */
    private $from = array();

    /**
     * array(array('email' => email, 'name' => 'name'))
     * @var array
     */
    private $tos = array();

    /**
     * Array of just email addresses
     * @var array
     */
    private $bccs = array();

    /**
     * array(array('email' => email, 'name' => 'name'))
     * @var array
     */
    private $ccs = array();

    /**
     * @var string
     */
    private $subject = '';

    /**
     * @var array
     */
    private $headers = array();

    /**
     * array('body' => body, 'charset' => charset)
     * @var array
     */
    private $text_part = null;

    /**
     * array('body' => body, 'charset' => charset)
     * @var array
     */
    private $html_part = null;

    /**
     * array('filename' => filename, 'path' => 'path on disk', 'data' => 'or binary data', 'type' => 'mimetype', 'disposition' => 'inline/attachment')
     * @var array
     */
    private $attachments = array();

    /**
     * @return RawMessage
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function setFrom($email, $name)
    {
        $this->from = array('email' => $email, 'name' => $name);
        return $this;
    }

    /**
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function addTo($email, $name)
    {
        $this->tos[] = array('email' => $email, 'name' => $name);
        return $this;
    }

    /**
     * @param string $email
     * @param string $name
     * @return $this
     */
    public function addCc($email, $name)
    {
        $this->ccs[] = array('email' => $email, 'name' => $name);
        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function addBcc($email)
    {
        $this->bccs[] = $email;
        return $this;
    }

    /**
     * @param string $subj
     * @return $this
     */
    public function setSubject($subj)
    {
        $this->subject = $subj;
        return $this;
    }

    /**
     * @param string $header
     * @param string $value
     * @return $this
     */
    public function addHeader($header, $value)
    {
        $this->headers[] = array('name' => $header, 'value' => $value);
        return $this;
    }

    /**
     * @param string $text
     * @param string $charset
     * @return $this
     */
    public function setTextBody($text, $charset)
    {
        $this->text_part = array('body' => $text, 'charset' => $charset);
        return $this;
    }

    /**
     * @param string $html
     * @param string $charset
     * @return $this
     */
    public function setHtmlBody($html, $charset)
    {
        $this->html_part = array('body' => $html, 'charset' => $charset);
        return $this;
    }

    /**
     * @return array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return array
     */
    public function getTos()
    {
        return $this->tos;
    }

    /**
     * @return array
     */
    public function getCcs()
    {
        return $this->ccs;

    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function hasTextPart()
    {
        return $this->text_part !== null;
    }

    /**
     * @return array|null
     */
    public function getTextPart()
    {
        return $this->body;
    }

    /**
     * @return bool
     */
    public function hasHtmlPart()
    {
        return $this->html_part !== null;
    }

    /**
     * @return array|null
     */
    public function getHtmlPart()
    {
        return $this->html_body;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}