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
	/**
	 * @var bool
	 */
	protected $_queue_hint = false;

	/**
	 * @var \Swift_Transport
	 */
	protected $force_transport;

	/**
	 * Metadata that might be used by the transports or queue processor
	 * @var array
	 */
	public $meta = array();

	public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
	{
		call_user_func_array(array($this, 'Swift_Mime_SimpleMessage::__construct'), \Swift_DependencyContainer::getInstance()->createDependenciesFor('mime.message'));

		if (!isset($charset)) {
			$charset = \Swift_DependencyContainer::getInstance()->lookup('properties.charset');
		}

		$this->setSubject($subject);
		$this->setBody($body);
		$this->setCharset($charset);
		if ($contentType) {
			$this->setContentType($contentType);
		}
	}


	/**
	 * Enable the queue hint that hints that it's okay to queue and send later.
	 */
	public function enableQueueHint()
	{
		$this->_queue_hint = true;
	}


	/**
	 * Is it okay to queue this message to send later?
	 *
	 * @return bool
	 */
	public function isQueueHinted()
	{
		return $this->_queue_hint;
	}


	/**
	 * Set a transport to use instead of whatever is configured in the mailer.
	 *
	 * @param \Swift_Transport $tr
	 */
	public function setForceTransport(\Swift_Transport $tr)
	{
		$this->force_transport = $tr;
	}


	/**
	 * Get the forced transport.
	 *
	 * @return \Swift_Transport
	 */
	public function getSpecificTransport()
	{
		return $this->force_transport;
	}


	/**
	 * @static
	 * @param null $subject
	 * @param null $body
	 * @param null $contentType
	 * @param null $charset
	 * @return \Orb\Mail\Message
	 */
	public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
	{
		return new self($subject, $body, $contentType, $charset);
	}
}
