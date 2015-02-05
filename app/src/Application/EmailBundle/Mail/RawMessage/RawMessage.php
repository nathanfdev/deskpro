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
     * array('filename' => filename, 'cid' => 'abc', 'tmp_path' => 'path on disk', 'bin_data' => 'or binary data', 'type' => 'mimetype')
     * @var array
     */
    private $attachments = array();

    /**
     * @param array $info
     * @return RawMessage
     */
    public static function newFromArray(array $info)
    {
        return new self(
            !empty($info['from']) ? $info['from'] : array(),
            !empty($info['tos']) ? $info['tos'] : array(),
            !empty($info['ccs']) ? $info['ccs'] : array(),
            !empty($info['bccs']) ? $info['bccs'] : array(),
            !empty($info['subject']) ? $info['subject'] : '',
            !empty($info['headers']) ? $info['headers'] : array(),
            !empty($info['text_part']) ? $info['text_part'] : null,
            !empty($info['html_part']) ? $info['html_part'] : null,
            !empty($info['attachments']) ? $info['attachments'] : array()
        );
    }

    /**
     * @param array  $from
     * @param array  $tos
     * @param array  $ccs
     * @param array  $bccs
     * @param string $subject
     * @param array  $headers
     * @param string $text_part
     * @param string $html_part
     * @param array  $attachments
     */
    public function __construct(array $from, array $tos, array $ccs, array $bccs, $subject, array $headers, $text_part, $html_part, array $attachments)
    {
        $this->from        = $from;
        $this->tos         = $tos;
        $this->bccs        = $bccs;
        $this->ccs         = $ccs;
        $this->subject     = $subject;
        $this->headers     = $headers;
        $this->text_part   = $text_part;
        $this->html_part   = $html_part;
        $this->attachments = $attachments;
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
    public function getBccs()
    {
        return $this->bccs;
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
     * @return array
     */
    public function getTextPart()
    {
        return $this->text_part;
    }

    /**
     * @return array
     */
    public function getHtmlPart()
    {
        return $this->html_part;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'from'        => $this->from,
            'tos'         => $this->tos,
            'ccs'         => $this->ccs,
            'bccs'        => $this->bccs,
            'subject'     => $this->subject,
            'headers'     => $this->headers,
            'text_part'   => $this->text_part,
            'html_part'   => $this->html_part,
            'attachments' => $this->attachments
        );
    }
}