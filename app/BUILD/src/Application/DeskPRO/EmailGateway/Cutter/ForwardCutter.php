<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter;

use Application\DeskPRO\App;
use DeskPRO\Component\Util\RegexUtils;
use Orb\Util\Strings;

/**
 * This works on an email message to detect a forwarded email, parse out
 * the original message and reply, and original author email/name.
 */
class ForwardCutter
{
    /** @var string */
    protected $body;
    /** @var bool */
    protected $is_html;

    /** @var string */
    protected $forwarded_message;
    /** @var array */
    protected $forward_info;
    /** @var string */
    protected $reply;

    /** @var string|null */
    protected $error_code = null;

    /**
     * @var \Application\DeskPRO\EmailGateway\Cutter\Def\ForwardDef
     */
    protected $cutter;

    /**
     * @return string
     */
    public static function getFwdSubjectRegex()
    {
        try {
            $regex = App::$container->getSetting('core_tickets.agent_fwd_subject_regex');
        } catch (\Exception $e) {
            $regex = false;
        }

        if ($regex) {
            $regex = Strings::getInputRegexPattern($regex);
        }

        if (!$regex) {
            $regex = '#^(FW|FWD|VL|WG|FS|VB|RV|VS|TR):#i';
        }

        return $regex;
    }

    /**
     * Check if a subject matches the pattern for a forwarded message.
     *
     * @param string $subject
     *
     * @return bool
     */
    public static function subjectIsForward($subject)
    {
        // Prefixes for FW/FWD and in other langs too
        return (bool) RegexUtils::safePregMatch(self::getFwdSubjectRegex(), ltrim($subject));
    }

    /**
     * Cut out the FWD prefix from subject.
     *
     * @param string $subject
     *
     * @return string
     */
    public static function cutSubjectForwardPrefix($subject)
    {
        return trim(RegexUtils::safePregReplace(self::getFwdSubjectRegex(), '', trim($subject)));
    }

    public function __construct($body, $is_html, $cutter)
    {
        $this->body    = $body;
        $this->is_html = $is_html;
        $this->cutter  = $cutter;

        if ($this->cutter instanceof Def\ForwardDef) {
            $this->_process();
        }
    }

    protected function _process()
    {
        $this->forward_info = $this->cutter->getForwardInfo($this->body, $this->is_html);

        if (!$this->forward_info['fwd_message_body']) {
            $this->error_code = 'unknown_body';
        } elseif (!$this->forward_info['fwd_from_email'] || !\Orb\Validator\StringEmail::isValueValid($this->forward_info['fwd_from_email']) || App::$container->getEmailAccountManager()->findAccountForEmailAddress($this->forward_info['fwd_from_email'])) {
            $this->error_code = 'unknown_email';
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->forward_info;
    }

    /**
     * Check if the forwarded message was read correctly and has all required information.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->error_code === null;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * Get the users message.
     *
     * @return string
     */
    public function getForwardedMessage()
    {
        return $this->forward_info['fwd_message_body'];
    }

    /**
     * Get the reply above the forwarded message.
     *
     * @return string
     */
    public function getReply()
    {
        return $this->forward_info['message_body'];
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress
     */
    public function getUserEmailItem()
    {
        $item        = new \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress();
        $item->email = $this->forward_info['fwd_from_email'];
        $item->name  = $this->getUserName();

        return $item;
    }

    /**
     * Get the user email address from the forwarded message.
     *
     * @return string
     */
    public function getUserEmailAddress()
    {
        return $this->forward_info['fwd_from_email'];
    }

    /**
     * Get the users name from the forwarded message (based on their name in From:).
     *
     * @return string
     */
    public function getUserName()
    {
        if (!empty($this->forward_info['fwd_from_name'])) {
            return $this->forward_info['fwd_from_name'];
        }

        return;
    }
}
