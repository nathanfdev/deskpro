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
 * @category Entities
 */

namespace Application\DeskPRO\Email\SendmailQueue;

use Application\DeskPRO\Entity\SendmailQueue;
use Orb\Util\Arrays;

/**
 * Simple wrapper around filter params
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
	private $statuses = array();

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
	 * @return $this
	 */
	public function setPage($page)
	{
		$this->page = max(1, (int)$page);
		return $this;
	}

	/**
	 * @param $per_page
	 * @return $this
	 */
	public function setPerPage($per_page)
	{
		$this->per_page = max(1, (int)$per_page);
		return $this;
	}

	/**
	 * @param array $statuses
	 * @return $this
	 */
	public function setStatuses(array $statuses)
	{
		$this->statuses = $statuses;
		return $this;
	}

	/**
	 * @param $status
	 * @return $this
	 * @throws \InvalidArgumentException
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
		static $valid = array(
			SendmailQueue::STATUS_INSERTED,
			SendmailQueue::STATUS_PROCESSING,
			SendmailQueue::STATUS_COMPLETE,
			SendmailQueue::STATUS_ERROR,
		);

		return $valid;
	}

	/**
	 * @param \DateTime $date
	 * @return $this
	 */
	public function setDateStart(\DateTime $date = null)
	{
		$this->date_start = $date;
		return $this;
	}

	/**
	 * @param \DateTime $date
	 * @return $this
	 */
	public function setDateEnd(\DateTime $date = null)
	{
		$this->date_end = $date;
		return $this;
	}

	/**
	 * @param $subject
	 * @return $this
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @param $from
	 * @return $this
	 */
	public function setFrom($from)
	{
		$this->from = $from;
		return $this;
	}

	/**
	 * @param $to
	 * @return $this
	 */
	public function setTo($to)
	{
		$this->to = $to;
		return $this;
	}

	/**
	 * @param $error_code
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