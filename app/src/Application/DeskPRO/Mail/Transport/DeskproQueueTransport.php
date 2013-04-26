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

namespace Application\DeskPRO\Mail\Transport;

use Orb\Util\Arrays;

class DeskproQueueTransport implements \Swift_Transport
{
    /**
	 * The event dispatcher from the plugin API
	 */
    private $_eventDispatcher;

	/**
	 * The address of the queue server
	 *
	 * @var \Swift_Transport_IoBuffer
	 */
	private $_buf;

	/**
	 * @var int
	 */
	private $server_port;

	/**
	 * @var string
	 */
	private $server_addr;

	/**
	 * @var array
	 */
	private $job_headers;

	private $next_job_headers;


    /**
     * Create a new MailTransport with the $log.
     *
     * @param \Swift_Events_EventDispatcher $eventDispatcher
     */
    public function __construct(\Swift_Transport_IoBuffer $buf, \Swift_Events_EventDispatcher $eventDispatcher)
    {
		$this->_buf = $buf;
        $this->_eventDispatcher = $eventDispatcher;

    }

	/**
	 * @param $server_addr
	 * @param $server_port
	 * @return DeskproQueueTransport
	 */
	public static function newInstance($server_addr, $server_port)
	{
		if (!\Swift_DependencyContainer::getInstance()->has('deskpro.queue_transport')) {
			\Swift_DependencyContainer::getInstance()
				->register('deskpro.queue_transport')
				->asNewInstanceOf('Application\\DeskPRO\\Mail\\Transport\\DeskproQueueTransport')
				->withDependencies(array(
					'transport.buffer',
					'transport.eventdispatcher'
				));
		}

		$args = \Swift_DependencyContainer::getInstance()->createDependenciesFor('deskpro.queue_transport');
		$obj = new self($args[0], $args[1]);
		$obj->setServer($server_addr, $server_port);
		return $obj;
	}

	/**
	 * @param $server_addr
	 * @param $server_port
	 */
	public function setServer($server_addr, $server_port)
	{
		$this->server_addr = $server_addr;
		$this->server_port = $server_port;
	}


	/**
	 * @param array $headers
	 * @param bool $next_only
	 */
	public function setJobHeaders(array $headers, $next_only = false)
	{
		if ($next_only) {
			$this->next_job_headers = $headers;
		} else {
			$this->job_headers = $headers;
		}
	}

    public function isStarted() { return false; }
    public function start() { }
    public function stop() {}

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param \Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
		$job_headers = $this->job_headers ?: array();
		if ($this->next_job_headers) {
			$job_headers = array_merge($job_headers, $this->next_job_headers);
		}
		$this->next_job_headers = null;

		if (!$job_headers) {
			$job_headers['created_at'] = time();
		}

        $failedRecipients = (array) $failedRecipients;

		if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

		#------------------------------
		# Init the buffer
		#------------------------------

		$params = array(
			'protocol' => 'tcp',
			'host' => $this->server_addr,
			'port' => $this->server_port,
			'timeout' => 30,
			'blocking' => 1,
			'tls' => false,
			'type' => \Swift_Transport_IoBuffer::TYPE_SOCKET
        );

		$this->_buf->initialize($params);

		#------------------------------
		# Generate special data block that prefixes the message
		#------------------------------

		$data = array(
			'subject'        => (string)$message->getSubject(),
			'from_addresses' => '',
			'to_addresses'   => array(),
			'cc_addresses'   => array(),
			'bcc_addresses'  => array(),
		);

		foreach (array(
			'from_addresses' => 'getFrom',
			'to_addresses'   => 'getTo',
			'cc_addresses'   => 'getCc',
			'bcc_addresses'  => 'getBcc'
		) as $k => $name) {
			$tos = $message->$name();
			if ($tos) {
				foreach ($tos as $addr => $x) {
					$data[$k][] = $addr;
				}
			}
		}

		$count = count($data['to_addresses']) + count($data['cc_addresses']) + count($data['bcc_addresses']);

		#------------------------------
		# Send data to queue server
		#------------------------------

		$header_string = Arrays::implodeTemplate($job_headers, "{KEY}: {VAL}\n");
		$this->_buf->write($header_string . "\n" . json_encode($data) . "\n\n");
		$message->toByteStream($this->_buf);
		$this->_buf->write("\n" . chr(4) . "\n");

		$result = '';
		while (true) {
			$x = $this->_buf->read(1024);
			if ($x === false || $x === null) {
				break;
			}
			$result .= $x;
		}

		$this->_buf->terminate();

		$success = strpos($result, 'DP_ACCEPT:') !== false;

		if ($success && $evt) {
			$evt->setResult(\Swift_Events_SendEvent::RESULT_SUCCESS);
			$evt->setFailedRecipients($failedRecipients);
			$this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
		}

        if (!$success && $evt) {
			$evt->setResult(\Swift_Events_SendEvent::RESULT_FAILED);
			$evt->setFailedRecipients($failedRecipients);
			$this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
		}

        return $count;
    }


    /**
     * Register a plugin.
     *
     * @param \Swift_Events_EventListener $plugin
     */
    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
    }
}