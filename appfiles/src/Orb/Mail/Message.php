<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Mail
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Mail;

use \Orb\Util\Strings;
use \Orb\Util\Util;

/**
 * Represents an email message to send
 */
class Message extends \Swift_Message
{
	protected $_queue_hint = false;

	/**
	 * Metadata that might be used by the transports or queue processor
	 * @var array
	 */
	public $meta = array();

	public function setQueueHint()
	{
		$this->_queue_hint = true;
	}

	public function isQueueHinted()
	{
		return $this->_queue_hint;
	}
}