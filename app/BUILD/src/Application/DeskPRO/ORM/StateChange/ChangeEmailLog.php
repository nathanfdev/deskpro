<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange;

class ChangeEmailLog implements ChangeInterface, NonStateTrackingInterface
{
    /**
     * @var string
     */
    private $field_id;

    /**
     * @var string
     */
    private $user_mode;

    /**
     * @var string
     */
    private $to_name;

    /**
     * @var string
     */
    private $to_email;

    /**
     * @var array
     */
    private $cc_emails;

    /**
     * @var string
     */
    private $from_name;

    /**
     * @var string
     */
    private $from_email;

    /**
     * @var string
     */
    private $template;

    /**
     * @var int
     */
    private $sendmail_source_id;

    /**
     * @param string   $field_id
     * @param string   $user_mode
     * @param string   $to_name
     * @param string   $to_email
     * @param string[] $cc_emails
     * @param string   $from_name
     * @param string   $from_email
     * @param string   $template
     * @param int      $sendmail_source_id
     */
    public function __construct($field_id, $user_mode, $to_name, $to_email, $cc_emails, $from_name, $from_email, $template, $sendmail_source_id = null)
    {
        $this->field_id           = $field_id;
        $this->user_mode          = $user_mode;
        $this->to_name            = $to_name;
        $this->to_email           = $to_email;
        $this->cc_emails          = $cc_emails ?: [];
        $this->from_name          = $from_name;
        $this->from_email         = $from_email;
        $this->template           = $template;
        $this->sendmail_source_id = $sendmail_source_id;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field_id;
    }

    /**
     * @return array
     */
    public function getOld()
    {
        return;
    }

    /**
     * @return array
     */
    public function getNew()
    {
        return [
            'field_id'           => $this->field_id,
            'user_mode'          => $this->user_mode,
            'to_name'            => $this->to_name,
            'to_email'           => $this->to_email,
            'cc_emails'          => $this->cc_emails,
            'from_name'          => $this->from_name,
            'from_email'         => $this->from_email,
            'template'           => $this->template,
            'sendmail_source_id' => $this->sendmail_source_id,
            'id_after'           => $this->sendmail_source_id,
        ];
    }

    /**
     * @return string
     */
    public function getUserMode()
    {
        return $this->user_mode;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->from_email;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->from_name;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getToEmail()
    {
        return $this->to_email;
    }

    /**
     * @return string
     */
    public function getToName()
    {
        return $this->to_name;
    }

    /**
     * @return array
     */
    public function getCcEmails()
    {
        return $this->cc_emails;
    }

    /**
     * @return int
     */
    public function getSendmailSourceId()
    {
        return $this->sendmail_source_id;
    }

    /**
     * @return bool
     */
    public function isSame()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isCollection()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isEntity()
    {
        return false;
    }
}
