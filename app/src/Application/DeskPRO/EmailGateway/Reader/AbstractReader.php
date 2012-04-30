<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\EmailGateway\Reader;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

abstract class AbstractReader
{
	protected $properties = array();

	public function setProperty($name, $value)
	{
		$this->properties[$name] = $value;
	}

	public function getProperty($name, $default = null)
	{
		return isset($this->properties[$name]) ? $this->properties[$name] : $default;
	}

	public function hasProperty($name)
	{
		return isset($this->properties[$name]);
	}

	public function setRawSource($source)
	{
		$this->_setRawSource($source);
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\BodyText
	 */
	public function getBodyText()
	{
		static $v;
		if (!$v) {
			$v = $this->_getBodyText();
		}

		return $v;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\BodyHtml
	 */
	public function getBodyHtml()
	{
		static $v;
		if (!$v) {
			$v = $this->_getBodyHtml();
		}

		return $v;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\Attachment[]
	 */
	public function getAttachments()
	{
		static $v;
		if (!$v) {
			$v = $this->_getAttachments();
		}

		return $v;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\Subject
	 */
	public function getSubject()
	{
		static $v;
		if (!$v) {
			$v = $this->_getSubject();
		}

		return $v;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress
	 */
	public function getFromAddress()
	{
		static $v;
		if (!$v) {
			$v = $this->_getFromAddress();
		}

		return $v;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
	 */
	public function getToAddresses()
	{
		static $v;
		if (!$v) {
			$v = $this->_getToAddresses();
		}

		return $v;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
	 */
	public function getCcAddresses()
	{
		static $v;
		if (!$v) {
			$v = $this->_getCcAddresses();
		}

		return $v;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\Header
	 */
	public function getHeader($header)
	{
		static $v = array();
		if (!isset($v[$header])) {
			$v[$header] = $this->_getHeader($header);
		}

		return $v[$header];
	}

	abstract protected function _setRawSource($source);
	abstract protected function _getBodyText();
	abstract protected function _getBodyHtml();
	abstract protected function _getAttachments();
	abstract protected function _getSubject();
	abstract protected function _getFromAddress();
	abstract protected function _getToAddresses();
	abstract protected function _getCcAddresses();
	abstract protected function _getHeader($header);

	/**
	 * Returns true if message marks itself as from a robot
	 *
	 * @return bool
	 */
	public function isFromRobot()
	{
		$auto = $this->getHeader('Auto-Submitted')->getAllParts();
		foreach ($auto as $v) {
			$v = strtolower($v);
			if ($v == 'auto-replied' || $v == 'auto-notified' || $v == 'auto-generated') {
				return true;
			}
		}

		return false;
	}
}
