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
	public $define_transport = false;
	public $pop3_options = array();
	public $gmail_options = array();

	public $gateway_type = 'tickets';
	public $is_enabled = true;
	public $address = '';

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 */
	protected $gateway;

	protected $new_addresses;
	protected $remove_addresses;
	protected $set_default_address = null;

	protected $persist_objs = array();
	protected $remove_objs = array();

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

		if ($gateway->linked_transport) {
			$this->define_transport = true;
			if ($this->connection_type == 'gmail' && $gateway->linked_transport->transport_type == 'gmail') {
				if ($this->gmail_options['username'] == $gateway->linked_transport->transport_options['username'] && $this->gmail_options['password'] == $gateway->linked_transport->transport_options['password']) {
					$this->define_transport = false;
				}
			}
		}

		foreach ($gateway->addresses as $adr) {
			$this->address = $adr->match_pattern;
			break;
		}
	}

	public function setNewAddresses(array $new_addresses)
	{
		$this->new_addresses = $new_addresses;
	}

	public function setRemoveAddressIds(array $remove_addresses)
	{
		$this->remove_addresses = $remove_addresses;
	}

	/**
	 * $desc should be a string type:pattern, like exact:test@example.com
	 *
	 * @param string $desc
	 */
	public function setDefaultAddress($desc)
	{
		$this->set_default_address = $desc;
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

		if ($this->remove_addresses) {
			foreach ($this->remove_addresses as $address_id) {
				if (!isset($this->gateway->addresses)) {
					continue;
				}

				$this->remove_objs[] = $this->gateway->addresses[$address_id];

				$this->gateway->addresses->remove($address_id);
				if ($this->gateway->default_address && $this->gateway->default_address->id == $address_id) {
					$this->gateway->default_address = null;
				}
			}
		}

		if ($this->new_addresses) {
			foreach ($this->new_addresses as $address) {
				$address->gateway = $this->gateway;
				$this->gateway->addresses->add($address);
			}
		}
	}


	public function save()
	{
		$this->apply();

		foreach ($this->remove_objs as $obj) {
			App::getOrm()->remove($obj);
		}

		App::getOrm()->persist($this->gateway);
		App::getOrm()->flush();
	}
}
