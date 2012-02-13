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
use Application\DeskPRO\EmailGateway\Reader\Item;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * The ezcParser uses the ezcMailParser class from ezComponents
 * to parse emails.
 *
 * @see http://ezcomponents.org/docs/api/trunk/introduction_Mail.html#mail-retrieval-and-parsing
 */
class EzcReader extends AbstractReader
{
	/**
	 * @var \ezcMailParser|null
	 */
	protected $parser = null;

	/**
	 * @var \ezcMail
	 */
	protected $mail = null;

	function __construct()
	{
		$opt = new \ezcMailParserOptions();

		$this->parser = new \ezcMailParser($opt);
	}

	protected function _setRawSource($source)
	{
		$set = new \ezcMailVariableSet($source);
		$this->mail = $this->parser->parseMail($set);
		$this->mail = $this->mail[0];
	}

	protected function _getHeader($name)
	{
		$name = strtolower($name);
		$header = new Item\Header();
		$header->name = $name;

		$parts = (array)$this->mail->getHeader($name, true);
		if ($parts) {
			foreach ($parts as $p) {
				$header->header_parts[] = \ezcMailTools::mimeDecode($name, 'utf-8');
			}
		}

		return $header;
	}

	protected function _getCcAddresses()
	{
		$emails = array();

		foreach ($this->mail->cc as $cc) {
			$email = new Item\EmailAddress();
			$email->name = $cc->name;
			$email->email = $cc->email;

			$emails[] = $email;
		}

		return $emails;
	}

	protected function _getToAddresses()
	{
		$emails = array();

		foreach ($this->mail->to as $to) {
			$email = new Item\EmailAddress();
			$email->name = $to->name;
			$email->email = $to->email;

			$emails[] = $email;
		}

		return $emails;
	}

	protected function _getFromAddress()
	{
		$email = new Item\EmailAddress();
		$email->name = $this->mail->from->name;
		$email->email = $this->mail->from->email;

		return $email;
	}

	protected function _getSubject()
	{
		$subject = new Item\Subject();
		$subject->subject = $this->mail->subject;

		return $subject;
	}

	protected function _getAttachments()
	{
		$attachments = array();

		foreach ($this->mail->fetchParts(array('ezcMailFile')) as $part) {
			$attach = new Item\Attachment();
			$attach->file_name = basename($part->fileName);
			$attach->mime_type = $part->mimeType;
			$attach->tmp_file = $part->fileName;

			$attachments[] = $attach;
		}

		return $attachments;
	}

	protected function _getBodyHtml()
	{
		foreach ($this->mail->fetchParts(array('ezcMailText')) as $part) {
			if ($part->subType == 'html') {
				$body = new Item\BodyHtml();
				$body->body = $part->text;
				$body->original_charset = $part->originalCharset;

				return $body;
			}
		}

		// Default to a blank body
		$body = new Item\BodyHtml();
		$body->body = '';
		$body->original_charset = 'us-ascii';
		return $body;
	}

	protected function _getBodyText()
	{
		foreach ($this->mail->fetchParts(array('ezcMailText')) as $part) {
			if ($part->subType == 'plain') {
				$body = new Item\BodyHtml();
				$body->body = $part->text;
				$body->original_charset = $part->originalCharset;

				return $body;
			}
		}

		// Default to a blank body
		$body = new Item\BodyHtml();
		$body->body = '';
		$body->original_charset = 'us-ascii';
		return $body;
	}
}