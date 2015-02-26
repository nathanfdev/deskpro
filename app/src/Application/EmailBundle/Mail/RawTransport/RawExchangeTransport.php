<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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


namespace Application\EmailBundle\Mail\RawTransport;


use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\ExchangeConfig;
use Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface;
use Psr\Log\LoggerInterface;

class RawExchangeTransport implements RawTransportInterface
{
	/**
	 * @var ExchangeConfig
	 */
	protected $config;

	/**
	 * @var RawMessageDecoderInterface
	 */
	protected $decoder;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \ExchangeWebServices
	 */
	protected $ews;

	public function __construct(ExchangeConfig $config, RawMessageDecoderInterface $decoder, LoggerInterface $logger)
	{
		$this->config = $config;
		$this->decoder = $decoder;
		$this->logger = $logger;
	}

	/**
	 * @return \ExchangeWebServices
	 */
	protected function ews()
	{
		return $this->ews
				? $this->ews
				: $this->ews = new \ExchangeWebServices($this->config->host, $this->config->user, $this->config->password);
	}

	public function sendRawMessage($from, array $tos, $raw_fp, array &$failed = null)
	{
		$sent = 0;

		if (!$tos) {
			return $sent;
		}

//		$raw = $this->decoder->createRawMessage($raw_fp);

		$msg = new \EWSType_MessageType();

//
//
//		$msg->Subject = $raw->getSubject();
//
//
//		$body = new \EWSType_BodyType();
//		$body->BodyType = 'TEXT';
//		$body->_ = $raw->getTextPart();
//
//		if ($html = $raw->getHtmlPart()) {
//			$body->BodyType = 'HTML';
//			$body->_ = $html;
//		}
//		$msg->Body = $body;
//
//
//		$msg->From = new \EWSType_SingleRecipientType();
//		$msg->From->Mailbox = new \EWSType_EmailAddressType();
//		$msg->From->Mailbox->EmailAddress = $from['email'];
//		$msg->From->Mailbox->Name = $from['name'];
//
//
//		$included = array();
//		$msg->ToRecipients = array();
//		foreach ($raw->getTos() as $to) {
//			$adds = new \EWSType_EmailAddressType();
//			$adds->Name = $to['name'];
//			$adds->EmailAddress = $to['email'];
//			$msg->ToRecipients[] = $adds;
//			$included[] = $to['email'];
//		}
//
//
//		$msg->CcRecipients = array();
//		foreach ($raw->getCcs() as $cc) {
//			$adds = new \EWSType_EmailAddressType();
//			$adds->Name = $cc['name'];
//			$adds->EmailAddress = $cc['email'];
//			$msg->CcRecipients[] = $adds;
//			$included[] = $to['email'];
//		}
//
//
//		$bccs = array_diff($tos, $included);
//		$msg->BccRecipients = array();
//		foreach ($bccs as $bcc) {
//			$adds = new \EWSType_EmailAddressType();
//			$adds->Name = $bcc['name'];
//			$adds->EmailAddress = $bcc['email'];
//			$msg->BccRecipients[] = $adds;
//		}
//
//
//		$_attachments = array();
//		foreach ($raw->getAttachments() as $attach) {
//			$at = new \EWSType_FileAttachmentType();
//			$at->Name = $attach['filename'];
//			$at->ContentType = $attach['type'];
//			$at->Content = $attach['bin_data'];
//			$_attachments[] = $at;
//			$msg->Attachments[] = $at;
//		}
//		if ($_attachments) {
//			$msg->Attachments = $_attachments;
//		}
//
//
//		$_headers = array();
//		foreach ($raw->getHeaders() as $header_name => $header_values) {
//			foreach ($header_values as $v) {
//				$hdr = new \EWSType_InternetHeaderType();
//				$hdr->HeaderName = $header_name;
//				$hdr->_ = $v;
//				$_headers[] = $hdr;
//			}
//		}
//		if ($_headers) {
//			$msg->InternetMessageHeaders = $_headers;
//		}
//

		$msg->MimeContent = new \EWSType_MimeContentType();
		$msg->MimeContent->_ = base64_encode(stream_get_contents($raw_fp, -1, 0));

		$msgRequest = new \EWSType_CreateItemType();
		$msgRequest->Items = new \EWSType_NonEmptyArrayOfAllItemsType();
		$msgRequest->Items->Message = $msg;
		$msgRequest->MessageDisposition = 'SendOnly';

		$this->logger->info('[RawExchangeTransport] Sending raw mail');
		$response = $this->ews()->CreateItem($msgRequest);

		if ($response && $response->ResponseMessages && ($response = $response->ResponseMessages->CreateItemResponseMessage)) {
			if ('Error' === $response->ResponseClass) {
				$this->logger->error(sprintf('[RawExchangeTransport] %s', $response->MessageText));
			} elseif ('Success' === $response->ResponseClass) {
				$this->logger->info('[RawExchangeTransport] success');
				$sent++;
			}
		}

		return $sent;
	}
}