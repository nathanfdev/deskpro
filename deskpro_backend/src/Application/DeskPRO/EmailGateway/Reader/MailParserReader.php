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
 * This reader uses php-mime-mail-parser with the PHP PECL MailParse
 * extension.
 *
 * @see http://code.google.com/p/php-mime-mail-parser/
 * @see http://pecl.php.net/package/mailparse
 */
class MailParserReader extends AbstractReader
{
	/**
	 * @var \MimeMailParser
	 */
	protected $parser = null;

	protected function _setRawSource($source)
	{
		$this->parser = new \MimeMailParser();
		$this->parser->setText($source);
	}

	protected function _getHeader($name)
	{
		$name = strtolower($name);
		$header = new Item\Header();
		$header->name = $name;
		$header->header_parts = (array)$this->parser->getHeader($name);

		return $header;
	}

	protected function _getCcAddresses()
	{
		$emails = array();

		$ccs = (array)$this->parser->getHeader('cc');
		foreach ($ccs as $cc) {
			$ezc_email  = \ezcMailTools::parseEmailAddress($cc);

			$email = new Item\EmailAddress();
			$email->name = $ezc_email->name;
			$email->email = $ezc_email->email;

			$emails[] = $email;
		}

		return $emails;
	}

	protected function _getToAddresses()
	{
		$to = $this->parser->getHeader('to');
		if (is_array($to)) $to = $to[0];

		$ezc_email  = \ezcMailTools::parseEmailAddress($to);

		$email = new Item\EmailAddress();
		$email->name = $ezc_email->name;
		$email->email = $ezc_email->email;

		return $email;
	}

	protected function _getFromAddress()
	{
		$from = $this->parser->getHeader('from');
		if (is_array($from)) $from = $from[0];

		$ezc_email  = \ezcMailTools::parseEmailAddress($from);

		$email = new Item\EmailAddress();
		$email->name = $ezc_email->name;
		$email->email = $ezc_email->email;

		return $email;
	}

	protected function _getSubject()
	{
		$subj = $this->parser->getHeader('subject');
		if (is_array($subj)) $subj = $subj[0];

		$subject = new Item\Subject();
		$subject->subject = $subj;

		return $subject;
	}

	protected function _getAttachments()
	{
		$attachments = array();

		$mail_attachments = $this->parser->getAttachments();

		foreach($mail_attachments as $mail_attach) {
			$attach = new Item\Attachment();
			$attach->file_name = $mail_attach->getFilename();
			$attach->mime_type = $mail_attach->getContentType();
			$attach->file_contents_callback = function () use ($mail_attach) {
				return $mail_attach->getContent();
			};

			$attachments[] = $attach;
		}

		return $attachments;
	}

	protected function _getBodyHtml()
	{
		$body = new Item\BodyHtml();
		$body->body = $this->parser->getMessageBody('html');
		$body->original_charset = '';

		return $body;
	}

	protected function _getBodyText()
	{
		$body = new Item\BodyHtml();
		$body->body = $this->parser->getMessageBody('text');
		$body->original_charset = '';

		return $body;
	}
}