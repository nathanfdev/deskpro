<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;

/**
 * Describes a mail transport
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\EmailTransport")
 * @ORM_Mapping\Table(name="email_transports")
 */
class EmailTransport extends \Application\DeskPRO\Domain\DomainObject
{
	const MATCH_TYPE_EXACT  = 'exact';
	const MATCH_TYPE_DOMAIN = 'domain';
	const MATCH_TYPE_REGEX  = 'regex';
	const MATCH_TYPE_ANY    = 'all';

	const TRANSPORT_TYPE_SMTP     = 'smtp';
	const TRANSPORT_TYPE_GMAIL    = 'gmail';
	const TRANSPORT_TYPE_SENDMAIL = 'sendmail';
	const TRANSPORT_TYPE_MAIL     = 'mail';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * Human friendly name for the transport. ie the account name for smtp etc
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="match_type", type="string", length=15)
	 */
	protected $match_type = 'exact';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="match_pattern", type="string", length=15)
	 */
	protected $match_pattern = '';

	/**
	 * The type of transport
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="transport_type", type="string", length=80)
	 */
	protected $transport_type;

	/**
	 * Options for the transport
	 *
	 * @ORM_Mapping\Column(name="transport_options", type="array")
	 */
	protected $transport_options = array();

	/**
	 * The type of transport
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="backup_transport_type", type="string", length=80)
	 */
	protected $backup_transport_type = '';

	/**
	 * Options for the transport
	 *
	 * @ORM_Mapping\Column(name="backup_transport_options", type="array")
	 */
	protected $backup_transport_options = array();

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="run_order", type="integer")
	 */
	protected $run_order = 0;

	/**
	 * @var \Swift_Transport
	 */
	protected $_transport = null;

	/**
	 * @var \Swift_Transport
	 */
	protected $_backup_transport = null;


	/**
	 * @return \Swift_Transport
	 */
	public function getTransport()
	{
		if ($this->_transport !== null) return $this->_transport;
		$this->_transport = self::createTransport($this->transport_type, $this->transport_options);
		return $this->_transport;
	}

	/**
	 * @return \Swift_Transport
	 */
	public function getBackupTransport()
	{
		if (!$this->backup_transport_type) return null;
		if ($this->_backup_transport !== null) return $this->_backup_transport;
		$this->_backup_transport = self::createTransport($this->backup_transport_type, $this->backup_transport_options);
		return $this->_backup_transport;
	}


	/**
	 * Check an email address to see if it matches this transport rule
	 *
	 * @param string $from_address
	 * @return bool
	 */
	public function doesMatchFromAddress($from_address)
	{
		$from_address = Strings::utf8_strtolower($from_address);

		switch ($this->match_type) {
			case self::MATCH_TYPE_ANY:
				return true;

			case self::MATCH_TYPE_DOMAIN:
				list (, $domain) = explode('@', $from_address);
				if ($this->match_pattern == $domain) {
					return true;
				}
				break;

			case self::MATCH_TYPE_EXACT:
				if ($this->match_pattern == $from_address) {
					return true;
				}
				break;

			case self::MATCH_TYPE_REGEX:
				if (preg_match($this->match_pattern, $from_address)) {
					return true;
				}
				break;
		}

		return false;
	}


	/**
	 * @var \Swift_Transport
	 */
	public static function createTransport($type, $options)
	{
		switch ($type) {
			case 'smtp':
				if (!$options['secure']) $options['secure'] = null;

				$tr = \Swift_SmtpTransport::newInstance($options['host'], $options['port'], $options['secure']);

				if (!empty($options['username']) OR !empty($options['password'])) {
					$tr->setUsername($options['username']);
					$tr->setPassword($options['password']);
				}

				break;

			case 'gmail':

				$options['host'] = 'smtp.gmail.com';
				$options['secure'] = 'ssl';
				$options['port'] = 465;

				$tr = \Swift_SmtpTransport::newInstance($options['host'], $options['port'], $options['secure']);
				$tr->setUsername($options['username']);
				$tr->setPassword($options['password']);

				break;

			case 'sendmail':
				$tr = \Swift_SendmailTransport::newInstance("{$options['sendmail_path']} -bs");
				break;

			case 'mail':
				$tr = \Swift_MailTransport::newInstance();
				break;

			default:
				throw new \InvalidArgumentException("Unknown transport type `{$type}`");
		}

		return $tr;
	}
}
