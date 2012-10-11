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
	protected $vals = array();
	protected $properties = array();
	protected $raw_source;
	protected $raw_headers;

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
		$this->raw_source = Strings::standardEol($source);

		$pos = strpos($this->raw_source, "\n\n");
		if ($pos) {
			$this->raw_headers = substr($this->raw_source, 0, $pos);
		}

		$this->_setRawSource($source);
	}

	public function getRawSource()
	{
		return $this->raw_source;
	}

	public function getRawHeaders()
	{
		return $this->raw_headers;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\BodyText
	 */
	public function getBodyText()
	{
		if (!isset($this->vals['body_text'])) {
			$this->vals['body_text'] = $this->_getBodyText();
		}

		return $this->vals['body_text'];
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\BodyHtml
	 */
	public function getBodyHtml()
	{
		if (!isset($this->vals['body_html'])) {
			$this->vals['body_html'] = $this->_getBodyHtml();
		}

		return $this->vals['body_html'];
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\Attachment[]
	 */
	public function getAttachments()
	{
		if (!isset($this->vals['attach'])) {
			$this->vals['attach'] = $this->_getAttachments();
		}

		return $this->vals['attach'];
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\Subject
	 */
	public function getSubject()
	{
		if (!isset($this->vals['subject'])) {
			$this->vals['subject'] = $this->_getSubject();
		}

		return $this->vals['subject'];
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress
	 */
	public function getFromAddress()
	{
		if (!isset($this->vals['from_address'])) {
			$this->vals['from_address'] = $this->_getFromAddress();
		}

		return $this->vals['from_address'];
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
	 */
	public function getToAddresses()
	{
		if (!isset($this->vals['to_address'])) {
			$this->vals['to_address'] = $this->_getToAddresses();
		}

		return $this->vals['to_address'];
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
	 */
	public function getCcAddresses()
	{
		if (!isset($this->vals['cc_addresses'])) {
			$this->vals['cc_addresses'] = $this->_getCcAddresses();
		}

		return $this->vals['cc_addresses'];
	}

	/**
	 * This is a collection of both To and CC addresses.
	 *
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress[]
	 */
	public function getDeliveredAddresses()
	{
		$to = $this->getToAddresses();
		$cc = $this->getCcAddresses();

		$all = array_merge($to, $cc);

		return $all;
	}

	/**
	 * @return string
	 */
	public function getOriginalTo()
	{
		$try = array(
			'Envelope-To',
			'X-Envelope-To',
			'Original-To',
			'X-Original-To',
			'Rcpt-Original',
			'X-Rcpt-Original'
		);

		foreach ($try as $header_name) {
			if (!($h = $this->getHeader($header_name))) {
				return null;
			}

			if (!$h->getHeader() || !\Orb\Validator\StringEmail::isValueValid($h->getHeader())) {
				continue;
			}

			return strtolower($h->getHeader());
		}

		return null;
	}

	/**
	 * @return \Application\DeskPRO\EmailGateway\Reader\Item\Header
	 */
	public function getHeader($header)
	{
		if (!isset($this->vals['headers']) || !isset($this->vals['headers'][$header])) {
			if (!isset($this->vals['headers'])) $this->vals['headers'] = array();
			$this->vals['headers'][$header] = $this->_getHeader($header);
		}

		return $this->vals['headers'][$header];
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
		if ($auto) {
			foreach ($auto as $v) {
				$v = strtolower($v);
				if (strpos($v, 'auto-replied') !== false || strpos($v, 'auto-notified') !== false || strpos($v, 'auto-generated') !== false) {
					return true;
				}
			}
		}

		$auto = $this->getHeader('X-Autoreply')->getAllParts();
		if ($auto) {
			foreach ($auto as $v) {
				$v = strtolower($v);
				if ($v == "1" || $v == "yes") {
					return true;
				}
			}
		}

		return false;
	}
}
