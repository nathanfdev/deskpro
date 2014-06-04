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
use Application\DeskPRO\EmailGateway\Reader\Item;

class ValueReader extends AbstractReader
{
	private $values = array();

	/**
	 * Sets values:
	 *
	 * @option array headers  An array of k=>array(values). k sholud be lowercase.
	 * @option array ccs      An array of email=>name
	 * @option array tos      An array of email=>name
	 * @option array from     email, or array(name, email)
	 * @option string subject
	 * @option string body_html
	 * @option string body_text
	 *
	 * @param array $values
	 */
	public function setValues(array $values)
	{
		$this->values = $values;
	}

	public function _kill()
	{

	}

	public function setRawSource($source)
	{
		throw new \RuntimeException();
	}

	public function getRawSource()
	{
		throw new \RuntimeException();
	}

	public function getRawHeaders()
	{
		throw new \RuntimeException();
	}

	protected function _setRawSource($x)
	{
		// Nothing
	}

	protected function _getHeader($name)
	{
		$name = strtolower($name);
		$header = new Item\Header();
		$header->name = $name;

		$parts = isset($this->values['headers'][$name]) ? $this->values['headers'][$name] : array();
		if ($parts) {
			foreach ($parts as $p) {
				$header->header_parts[] = $p;
			}
		}

		return $header;
	}

	protected function _getCcAddresses()
	{
		$emails = array();

		$val_emails = isset($this->values['ccs']) ? $this->values['ccs'] : array();

		foreach ($val_emails as $name => $eml) {
			$email = new Item\EmailAddress();
			$email->name = $name;
			$email->name_utf8 = $name;
			$email->email = $eml;
			$email->original_charset = 'UTF-8';

			$emails[] = $email;
		}

		return $emails;
	}

	protected function _getToAddresses()
	{
		$emails = array();

		$val_emails = isset($this->values['tos']) ? $this->values['tos'] : array();

		foreach ($val_emails as $name => $eml) {
			$email = new Item\EmailAddress();
			$email->name = $name;
			$email->name_utf8 = $name;
			$email->email = $eml;
			$email->original_charset = 'UTF-8';

			$emails[] = $email;
		}

		return $emails;
	}

	protected function _getFromAddress()
	{
		if (!isset($this->values['from'])) {
			$email = new Item\EmailAddress();
			$email->name = '';
			$email->name_utf8 = '';
			$email->email = '';
			return $email;
		}

		if (is_array($this->values['from'])) {
			if (isset($this->values['from'][0])) {
				$name = $this->values['from'][0];
				$eml = $this->values['from'][1];
			} else {
				$name = $this->values['from']['name'];
				$eml = $this->values['from']['email'];
			}
		} else {
			$name = '';
			$eml = $this->values['from'];
		}

		$email = new Item\EmailAddress();
		$email->name = $name;
		$email->name_utf8 = $name;
		$email->email = $eml;

		return $email;
	}

	protected function _getReplyToAddress()
	{
		return false;
	}

	protected function _getOriginalFromAddress()
	{
		return false;
	}

	protected function _getSubject()
	{
		if (!isset($this->values['subject'])) {
			$subject = new Item\Subject();
			$subject->subject = '';
			$subject->subject_utf8 = '';
			$subject->original_charset = 'UTF-8';
			return $subject;
		}

		$subject = new Item\Subject();
		$subject->subject = $this->values['subject'];
		$subject->subject_utf8 = $this->values['subject'];
		$subject->original_charset = 'UTF-8';

		return $subject;
	}

	protected function _getOriginalSubject()
	{
		$header = $this->getHeader('Thread-Topic');
		if (!$header || empty($header->header_parts)) {
			return null;
		}

		$subject = new Item\Subject();
		$subject->subject = $header->getHeader();
		$subject->subject_utf8 = $subject->subject;
		$subject->original_charset = 'UTF-8';

		return $subject;
	}

	protected function _getAttachments()
	{
		//todo
		return array();
	}

	protected function _getBodyHtml()
	{
		$body = new Item\BodyHtml();
		$body->body = isset($this->values['body_html']) ? $this->values['body_html'] : '';
		$body->body_utf8 = $body->body;
		$body->original_charset = 'UTF-8';
		return $body;
	}

	protected function _getBodyText()
	{
		$body = new Item\BodyHtml();
		$body->body = isset($this->values['body_text']) ? $this->values['body_text'] : '';
		$body->body_utf8 = $body->body;
		$body->original_charset = 'UTF-8';
		return $body;
	}
}
