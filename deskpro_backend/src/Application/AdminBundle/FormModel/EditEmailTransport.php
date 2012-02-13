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
use Application\DeskPRO\Entity\EmailTransport;

use Orb\Util\Arrays;

class EditEmailTransport
{
	public $match_type;
	public $match_email;
	public $match_domain;
	public $match_regex;

	public $transport_type;
	public $smtp_options = array();
	public $gmail_options = array();

	public $backup_transport_type;
	public $backup_smtp_options = array();
	public $backup_gmail_options = array();

	/**
	 * @var \Application\DeskPRO\Entity\EmailTransport
	 */
	protected $transport;

	public function __construct(EmailTransport $transport)
	{
		$this->transport = $transport;

		$this->match_type = $transport->match_type;
		if ($this->match_type == 'exact') {
			$this->match_email = $transport->match_pattern;
		} elseif ($this->match_type == 'domain') {
			$this->match_domain = $transport->match_pattern;
		} elseif ($this->match_type == 'regex') {
			$this->match_regex = $transport->match_pattern;
		}

		if ($transport->transport_type == 'smtp') {
			$this->smtp_options = $transport->transport_options;
		} elseif ($transport->transport_type == 'gmail') {
			$this->gmail_options = $transport->transport_options;
		}

		if ($transport->backup_transport_type == 'smtp') {
			$this->backup_smtp_options = $transport->backup_transport_options;
		} elseif ($transport->backup_transport_type == 'gmail') {
			$this->backup_gmail_options = $transport->backup_transport_options;
		}

		if (!isset($this->smtp_options['port'])) $this->smtp_options['port'] = 25;
		if (!isset($this->backup_smtp_options['port'])) $this->backup_smtp_options['port'] = 25;
	}

	public function apply()
	{
		$this->transport->match_type = $this->match_type;

		if ($this->match_email) {
			$this->transport->match_pattern = $this->match_email;
		} elseif ($this->match_domain) {
			$this->transport->match_pattern = $this->match_domain;
		} elseif ($this->match_regex) {
			$this->transport->match_pattern = $this->match_regex;
		}

		$this->transport->transport_type = $this->transport_type;
		if ($this->transport_type == 'smtp') {
			$this->transport->title = $this->smtp_options['host'] . ':' . $this->smtp_options['username'];
			$this->transport->transport_options = $this->smtp_options;
		} elseif ($this->transport_type == 'gmail') {
			$this->transport->title = 'Google Apps: ' . $this->gmail_options['username'];
			$this->transport->transport_options = $this->gmail_options;
		} else {
			$this->transport->title = 'PHP mail()';
		}

		if ($this->backup_transport_type) {
			$this->transport->backup_transport_type = $this->backup_transport_type;
			if ($this->backup_transport_type == 'smtp') {
				$this->transport->backup_transport_options = $this->backup_smtp_options;
			} elseif ($this->backup_transport_type == 'gmail') {
				$this->transport->backup_transport_options = $this->backup_gmail_options;
			} else {
				$this->transport->backup_transport_type = '';
				$this->transport->backup_transport_options = array();
			}
		}

		if ($this->match_type == 'any') {
			$this->transport->run_order = 100000000;
		}
	}


	public function save()
	{
		$this->apply();

		App::getOrm()->persist($this->transport);
		App::getOrm()->flush();
	}
}
