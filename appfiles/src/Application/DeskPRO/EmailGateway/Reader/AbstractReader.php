<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Reader;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

abstract class AbstractReader
{
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
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress
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
}