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
	 * @param string $field_id
	 * @param string $user_mode
	 * @param string $to_name
	 * @param string $to_email
	 * @param string[] $cc_emails
	 * @param string $from_name
	 * @param string $from_email
	 * @param string $template
	 */
	public function __construct($field_id, $user_mode, $to_name, $to_email, $cc_emails, $from_name, $from_email, $template)
	{
		$this->field_id   = $field_id;
		$this->user_mode  = $user_mode;
		$this->to_name    = $to_name;
		$this->to_email   = $to_email;
		$this->cc_emails  = $cc_emails ?: array();
		$this->from_name  = $from_name;
		$this->from_email = $from_email;
		$this->template   = $template;
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
		return null;
	}


	/**
	 * @return array
	 */
	public function getNew()
	{
		return array(
			'field_id'   => $this->field_id,
			'user_mode'  => $this->user_mode,
			'to_name'    => $this->to_name,
			'to_email'   => $this->to_email,
			'cc_emails'  => $this->cc_emails,
			'from_name'  => $this->from_name,
			'from_email' => $this->from_email,
			'template'   => $this->template,
		);
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