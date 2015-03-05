<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace Application\DeskPRO\Templating\Templates;

/**
 * Represents special "email" template code.
 *
 * <code>
 *     <dp:subject>My Subject</dp:subject>
 *     And here is my body.
 * </code>
 *
 * @package Application\DeskPRO\Templating\Templates
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
        $this->body = '';

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
     * @param $subject
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
            . $this->subject
            . self::SUBJ_TOKEN_END
            . "\n"
            . $this->body
        ;

        return $code;
    }


    /**
     * @param  string                    $code
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
            throw new \InvalidArgumentException("Invalid subject tags: Missing end tag");
        }
        if ($subj_start === false && $subj_end !== false) {
            throw new \InvalidArgumentException("Invalid subject tags: Missing start tag");
        }

        // Has a subject
        if ($subj_start !== false && $subj_end !== false) {

            if ($subj_start > $subj_end) {
                throw new \InvalidArgumentException("Invalid subject tags: Start tag after end tag");
            }

            $subj_len = $subj_end - ($subj_start + $subj_start_len);
            $this->subject = substr($code, $subj_start+$subj_start_len, $subj_len);

            // Subject at beginning
            if ($subj_start === 0) {
                $this->body = substr($code, $subj_end+$subj_end_len);

            // Subject wrapped somewhere weirdly
            } else {
                $this->body = trim(substr($code, 0, $subj_start))
                    . "\n"
                    . trim(substr($code, $subj_end+$subj_end_len));
            }

        // No Subject
        } else {
            $this->subject = '';
            $this->body = $code;
        }

        $this->subject = trim($this->subject);
        $this->body    = trim($this->body);
    }
}
