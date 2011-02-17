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

	public function enableQueueHint()
	{
		$this->_queue_hint = true;
	}

	public function isQueueHinted()
	{
		return $this->_queue_hint;
	}

	public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
	{
		return new self($subject, $body, $contentType, $charset);
	}
}