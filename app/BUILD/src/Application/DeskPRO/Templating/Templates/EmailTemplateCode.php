<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Templating\Templates;

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

        $subj_start = strpos($code, self::SUBJ_TOKEN_START);
        $subj_end   = strrpos($code, self::SUBJ_TOKEN_END);

        $subj_start_len = strlen(self::SUBJ_TOKEN_START);
        $subj_end_len   = strlen(self::SUBJ_TOKEN_END);

        if ($subj_start !== false && $subj_end === false) {
            throw new \InvalidArgumentException('Invalid subject tags: Missing end tag');
        }
        if ($subj_start === false && $subj_end !== false) {
            throw new \InvalidArgumentException('Invalid subject tags: Missing start tag');
        }

        // Has a subject
        if ($subj_start !== false && $subj_end !== false) {
            if ($subj_start > $subj_end) {
                throw new \InvalidArgumentException('Invalid subject tags: Start tag after end tag');
            }

            $subj_len      = $subj_end - ($subj_start + $subj_start_len);
            $this->subject = substr($code, $subj_start + $subj_start_len, $subj_len);

            // Subject at beginning
            if ($subj_start === 0) {
                $this->body = substr($code, $subj_end + $subj_end_len);

            // Subject wrapped somewhere weirdly
            } else {
                $this->body = trim(substr($code, 0, $subj_start))
                    ."\n"
                    .trim(substr($code, $subj_end + $subj_end_len));
            }

        // No Subject
        } else {
            $this->subject = '';
            $this->body    = $code;
        }

        $this->subject = trim($this->subject);
        $this->body    = trim($this->body);
    }
}
