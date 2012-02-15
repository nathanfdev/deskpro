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

		// Dont have ezc try and convert charsets, we'll handle that ourselves tyvm
		\ezcMailCharsetConverter::setConvertMethod(function($text) {
			return $text;
		});
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
			if (!$cc->charset) $cc->charset = 'us-ascii';

			$email = new Item\EmailAddress();
			$email->name = $cc->name;
			$email->name_utf8 = Strings::convertToUtf8($cc->name, $cc->charset);
			$email->email = $cc->email;
			$email->original_charset = $cc->charset;

			$emails[] = $email;
		}

		return $emails;
	}

	protected function _getToAddresses()
	{
		$emails = array();

		foreach ($this->mail->to as $to) {
			if (!$to->charset) $cc->charset = 'us-ascii';

			$email = new Item\EmailAddress();
			$email->name = $to->name;
			$email->name_utf8 = Strings::convertToUtf8($to->name, $to->charset);
			$email->email = $to->email;
			$email->original_charset = $to->charset;

			$emails[] = $email;
		}

		return $emails;
	}

	protected function _getFromAddress()
	{
		if (!$this->mail->from->charset) $this->mail->from->charset = 'us-ascii';

		$email = new Item\EmailAddress();
		$email->name = $this->mail->from->name;
		$email->name_utf8 = Strings::convertToUtf8($this->mail->from->name, $this->mail->from->charset);
		$email->email = $this->mail->from->email;

		return $email;
	}

	protected function _getSubject()
	{
		if (!$this->mail->subjectCharset) $this->mail->subjectCharset = 'us-ascii';

		$subject = new Item\Subject();
		$subject->subject = $this->mail->subject;
		$subject->subject_utf8 = Strings::convertToUtf8($this->mail->subject, $this->mail->subjectCharset);
		$subject->original_charset = $this->mail->subjectCharset;

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
				if (!$part->originalCharset) $part->originalCharset = 'us-ascii';

				$body = new Item\BodyHtml();
				$body->body = $part->text;
				$body->body_utf8 = Strings::convertToUtf8($part->text, $part->originalCharset);
				$body->original_charset = $part->originalCharset;

				return $body;
			}
		}

		// Default to a blank body
		$body = new Item\BodyHtml();
		$body->body = '';
		$body->original_charset = 'UTF-8';
		return $body;
	}

	protected function _getBodyText()
	{
		foreach ($this->mail->fetchParts(array('ezcMailText')) as $part) {
			if ($part->subType == 'plain') {
				if (!$part->originalCharset) $part->originalCharset = 'us-ascii';

				$body = new Item\BodyHtml();
				$body->body = $part->text;
				$body->body_utf8 = Strings::convertToUtf8($part->text, $part->originalCharset);
				$body->original_charset = $part->originalCharset;

				return $body;
			}
		}

		// Default to a blank body
		$body = new Item\BodyHtml();
		$body->body = '';
		$body->original_charset = 'UTF-8';
		return $body;
	}
}
