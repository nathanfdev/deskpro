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

namespace Application\EmailBundle\Templating\Templates;

use Application\DeskPRO\Entity\Blob;

/**
 * Represents special "email" template code.
 *
 * <code>
 *     <dp:subject>My Subject</dp:subject>
 *     And here is my body.
 * </code>
 */
class EmailTemplateCode extends TemplateCode
{
    const SUBJ_TOKEN_START = '<dp:subject>';
    const SUBJ_TOKEN_END   = '</dp:subject>';

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var Blob[]
     */
    private $attachments = [];

    /**
     * @param string $code
     */
    public function __construct($code = null)
    {
        $this->subject = '';
        $this->body    = '';

        if ($code) {
            $this->setCode($code);
        }
    }

    /**
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = trim($subject);
    }

    /**
     * @param $body
     */
    public function setBody($body)
    {
        $this->body = trim($body);
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        $code =
            self::SUBJ_TOKEN_START
            .$this->subject
            .self::SUBJ_TOKEN_END
            ."\n"
            .$this->body
        ;

        return $code;
    }

    /**
     * @param string $code
     *
     * @throws \InvalidArgumentException
     */
    public function setCode($code)
    {
        parent::setCode($code);
        $code = trim($code);

        $subjStart = strpos($code, self::SUBJ_TOKEN_START);
        $subjEnd   = strrpos($code, self::SUBJ_TOKEN_END);

        $subjStartLen = strlen(self::SUBJ_TOKEN_START);
        $subjEndLen   = strlen(self::SUBJ_TOKEN_END);

        if ($subjStart !== false && $subjEnd === false) {
            throw new \InvalidArgumentException('Invalid subject tags: Missing end tag');
        }
        if ($subjStart === false && $subjEnd !== false) {
            throw new \InvalidArgumentException('Invalid subject tags: Missing start tag');
        }

        // Has a subject
        if ($subjStart !== false && $subjEnd !== false) {
            if ($subjStart > $subjEnd) {
                throw new \InvalidArgumentException('Invalid subject tags: Start tag after end tag');
            }

            $subjLen       = $subjEnd - ($subjStart + $subjStartLen);
            $this->subject = substr($code, $subjStart + $subjStartLen, $subjLen);

            // Subject at beginning
            if ($subjStart === 0) {
                $this->body = substr($code, $subjEnd + $subjEndLen);

            // Subject wrapped somewhere weirdly
            } else {
                $this->body = trim(substr($code, 0, $subjStart))
                    ."\n"
                    .trim(substr($code, $subjEnd + $subjEndLen));
            }

        // No Subject
        } else {
            $this->subject = '';
            $this->body    = $code;
        }

        $this->subject = trim($this->subject);
        $this->body    = trim($this->body);
    }

    /**
     * @return Blob[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param Blob[] $attachments
     *
     * @return EmailTemplateCode
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @param Blob $attachment
     *
     * @return $this
     */
    public function addAttachment(Blob $attachment)
    {
        $this->attachments[$attachment->getId()] = $attachment;

        return $this;
    }
}
