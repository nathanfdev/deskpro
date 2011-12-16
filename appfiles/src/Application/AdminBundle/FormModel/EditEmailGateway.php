<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\FormModel;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\EmailGateway;

use Orb\Util\Arrays;

class EditEmailGateway
{
	public $connection_type = 'pop3';
	public $pop3_options = array();
	public $gmail_options = array();

	public $gateway_type = 'tickets';
	public $is_enabled = true;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 */
	protected $gateway;

	public function __construct(EmailGateway $gateway)
	{
		$this->gateway = $gateway;

		$this->connection_type = $gateway->connection_type;
		if (!$this->connection_type || $this->connection_type == 'pop3') {
			$this->pop3_options = $gateway->connection_options;

			if (!isset($this->pop3_options['port'])) {
				$this->pop3_options['port'] = '110';
			}
		} elseif ($this->connection_type == 'gmail') {
			$this->gmail_options = $gateway->connection_options;
		}

		$this->gateway_type = $gateway->gateway_type;
		$this->is_enabled = $gateway->is_enabled;
	}

	public function apply()
	{
		$this->gateway->connection_type = $this->connection_type;
		if ($this->connection_type == 'pop3') {
			$this->gateway->connection_options = $this->pop3_options;
			$this->gateway->title = "{$this->pop3_options['host']} : {$this->pop3_options['username']}";
		} elseif ($this->connection_type == 'gmail') {
			$this->gateway->connection_options = $this->gmail_options;
			$this->gateway->title = "Google Apps : {$this->gmail_options['username']}";
		}

		$this->gateway->gateway_type = $this->gateway_type;
		$this->gateway->is_enabled = $this->is_enabled;
	}


	public function save()
	{
		$this->apply();

		App::getOrm()->persist($this->gateway);
		App::getOrm()->flush();
	}
}
