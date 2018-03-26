<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\SendmailSource;

use Application\EmailBundle\Entity\SendmailSource;
use Orb\Util\Arrays;

/**
 * Simple wrapper around filter params.
 */
class FinderFilter
{
    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var int
     */
    private $per_page = 100;

    /**
     * @var array
     */
    private $statuses = [];

    /**
     * @var null|\DateTime
     */
    private $date_start = null;

    /**
     * @var null|\DateTime
     */
    private $date_end = null;

    /**
     * @var string
     */
    private $subject = '';

    /**
     * @var string
     */
    private $from = '';

    /**
     * @var string
     */
    private $to = '';

    /**
     * @var string
     */
    private $error_code = '';

    /**
     * @param $page
     *
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = max(1, (int) $page);

        return $this;
    }

    /**
     * @param $per_page
     *
     * @return $this
     */
    public function setPerPage($per_page)
    {
        $this->per_page = max(1, (int) $per_page);

        return $this;
    }

    /**
     * @param array $statuses
     *
     * @return $this
     */
    public function setStatuses(array $statuses)
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @param $status
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function addStatus($status)
    {
        if (!in_array($status, $this->getValidStatuses())) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }

        Arrays::pushUnique($this->statuses, $status);

        return $this;
    }

    /**
     * @param $status
     */
    public function removeStatus($status)
    {
        $this->statuses = Arrays::removeValue($this->statuses, $status);

        return $this;
    }

    /**
     * @return array
     */
    public function getValidStatuses()
    {
        static $valid = [
            SendmailSource::STATUS_INSERTED,
            SendmailSource::STATUS_PENDING,
            SendmailSource::STATUS_PROCESSING,
            SendmailSource::STATUS_COMPLETE,
            SendmailSource::STATUS_ERROR,
            SendmailSource::STATUS_RETRY,
            SendmailSource::STATUS_ABORTED,
        ];

        return $valid;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateStart(\DateTime $date = null)
    {
        $this->date_start = $date;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateEnd(\DateTime $date = null)
    {
        $this->date_end = $date;

        return $this;
    }

    /**
     * @param $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param $from
     *
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param $to
     *
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param $error_code
     *
     * @return $this
     */
    public function setErrorCode($error_code)
    {
        $this->error_code = $error_code;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateEnd()
    {
        return $this->date_end;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateStart()
    {
        return $this->date_start;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
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
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->per_page;
    }
}
