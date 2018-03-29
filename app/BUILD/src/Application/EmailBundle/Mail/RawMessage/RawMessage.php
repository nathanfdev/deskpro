<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Mail\RawMessage;

/**
 * Describes a raw RFC2822 message as an object.
 *
 * Note: Why no BCC? Because BCC's are by definition not part of the email.
 * You need to track your BBC's elsewhere.
 */
class RawMessage
{
    /**
     * array('email' => email, 'name' => 'name').
     *
     * @var array
     */
    private $from = [];

    /**
     * array(array('email' => email, 'name' => 'name')).
     *
     * @var array
     */
    private $tos = [];

    /**
     * array(array('email' => email, 'name' => 'name')).
     *
     * @var array
     */
    private $ccs = [];

    /**
     * @var string
     */
    private $subject = '';

    /**
     * Note: All headers are always arrays, even if there is only 1 value.
     *
     * array('Header-Name' => array('Value1', 'Value2'));
     *
     * @var array
     */
    private $headers = [];

    /**
     * array('body' => body, 'charset' => charset).
     *
     * @var array
     */
    private $text_part = null;

    /**
     * array('body' => body, 'charset' => charset).
     *
     * @var array
     */
    private $html_part = null;

    /**
     * array('filename' => filename, 'cid' => 'abc', 'tmp_path' => 'path on disk', 'bin_data' => 'or binary data', 'type' => 'mimetype').
     *
     * @var array
     */
    private $attachments = [];

    /**
     * @param array $info
     *
     * @return RawMessage
     */
    public static function newFromArray(array $info)
    {
        return new self(
            !empty($info['from']) ? $info['from'] : [],
            !empty($info['tos']) ? $info['tos'] : [],
            !empty($info['ccs']) ? $info['ccs'] : [],
            !empty($info['subject']) ? $info['subject'] : '',
            !empty($info['headers']) ? $info['headers'] : [],
            !empty($info['text_part']) ? $info['text_part'] : null,
            !empty($info['html_part']) ? $info['html_part'] : null,
            !empty($info['attachments']) ? $info['attachments'] : []
        );
    }

    /**
     * @param array  $from
     * @param array  $tos
     * @param array  $ccs
     * @param string $subject
     * @param array  $headers
     * @param string $text_part
     * @param string $html_part
     * @param array  $attachments
     */
    public function __construct(array $from, array $tos, array $ccs, $subject, array $headers, $text_part, $html_part, array $attachments)
    {
        $this->from        = $from;
        $this->tos         = $tos;
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
        return [
            'from'        => $this->from,
            'tos'         => $this->tos,
            'ccs'         => $this->ccs,
            'subject'     => $this->subject,
            'headers'     => $this->headers,
            'text_part'   => $this->text_part,
            'html_part'   => $this->html_part,
            'attachments' => $this->attachments,
        ];
    }
}
