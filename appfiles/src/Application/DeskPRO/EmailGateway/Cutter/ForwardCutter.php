<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Cutter;

/**
 * This works on an email message to detect a forwarded email, parse out
 * the original message and reply, and original author email/name.
 */
class ForwardCutter
{
	protected $body;
	protected $is_html;
	protected $is_valid = false;

	protected $forwarded_message;
	protected $forward_info;
	protected $reply;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Cutter\Def\ForwardDef
	 */
	protected $cutter;


	/**
	 * Check if a subject matches the pattern for a forwarded message.
	 * 
	 * @param string $subject
	 * @return bool
	 */
	public static function subjectIsForward($subject)
	{
		// Prefixes for FW/FWD and in other langs too
		return (bool)preg_match('#^(FW|FWD|VL|WG|FS|VB|RV|VS):#i', ltrim($subject));
	}


	/**
	 * Cut out the FWD prefix from subject
	 * 
	 * @param string $subject
	 * @return string
	 */
	public static function cutSubjectForwardPrefix($subject)
	{
		return preg_replace('#^(FW|FWD|VL|WG|FS|VB|RV|VS):\s*#i', '', trim($subject));
	}

	
	public function __construct($body, $is_html, $cutter)
	{
		$this->body = $body;
		$this->is_html = $is_html;
		$this->cutter = $cutter;

		if ($this->cutter instanceof Def\ForwardCutter) {
			$this->_process();
		}
	}

	protected function _process()
	{
		$this->forwarded_message = $this->cutter->getForwardedMessage($this->body, $this->is_html);
		$this->forward_info      = $this->cutter->getForwardInfo($this->body, $this->is_html);
		$this->reply             = $this->cutter->cutForwardBlock($this->body, $this->is_html);

		if ($this->forwarded_message && !empty($this->forward_info['from_email'])) {
			$this->is_valid = true;
		}
	}


	/**
	 * Check if the forwarded message was read correctly and has all required information
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		return $this->is_valid;
	}



	/**
	 * Get the users message
	 * 
	 * @return string
	 */
	public function getForwardedMessage()
	{
		return $this->forwarded_message;
	}

	
	/**
	 * Get the reply above the forwarded message
	 * 
	 * @return string
	 */
	public function getReply()
	{
		return $this->reply;
	}


	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress
	 */
	public function getUserEmailItem()
	{
		$item = new \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress();
		$item->email = $this->forward_info['from_email'];
		$item->name  = $this->getUserName();

		return $item;
	}


	/**
	 * Get the user email address from the forwarded message
	 * 
	 * @return string
	 */
	public function getUserEmailAddress()
	{
		return $This->forward_info['from_email'];
	}


	/**
	 * Get the users name from the forwarded message (based on their name in From:)
	 * 
	 * @return string
	 */
	public function getUserName()
	{
		if (!empty($this->forward_info['from_name'])) {
			return $this->forward_info['from_name'];
		}

		return null;
	}
}