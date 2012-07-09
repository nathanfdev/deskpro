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
		\ezcMailParser::setTmpDir(null);

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
				$header->header_parts[] = \ezcMailTools::mimeDecode($p, 'utf-8');
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
		if (!$this->mail->from || !$this->mail->from->email) {
			$email = new Item\EmailAddress();
			$email->name = '';
			$email->name_utf8 = '';
			$email->email = '';
			return $email;
		}

		if (!$this->mail->from->charset) $this->mail->from->charset = 'us-ascii';

		$email = new Item\EmailAddress();
		$email->name = $this->mail->from->name;
		$email->name_utf8 = Strings::convertToUtf8($this->mail->from->name, $this->mail->from->charset);
		$email->email = $this->mail->from->email;

		return $email;
	}

	protected function _getSubject()
	{
		if (!$this->mail->subject) {
			$subject = new Item\Subject();
			$subject->subject = '';
			$subject->subject_utf8 = '';
			$subject->original_charset = 'UTF-8';
			return $subject;
		}

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
			$attach->file_name  = basename($part->fileName);
			$attach->tmp_file   = $part->fileName;
			$attach->mime_type  = \Orb\Data\ContentTypes::getContentTypeFromFilename($attach->file_name);

			$attach->content_id = $part->getHeader('Content-ID');
			if ($attach->content_id) {
				// Content-ID is enclosed in brackets, remove those
				$attach->content_id = preg_replace('#^<(.*?)>$#', '$1', $attach->content_id);
			}

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

				$body = new Item\BodyText();
				$body->body = $part->text;
				$body->body_utf8 = Strings::convertToUtf8($part->text, $part->originalCharset);
				$body->original_charset = $part->originalCharset;

				return $body;
			}
		}

		// Default to a blank body
		$body = new Item\BodyText();
		$body->body = '';
		$body->original_charset = 'UTF-8';
		return $body;
	}
}
