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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;

/**
 * Describes a mail transport
 *
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
	 */
	protected $id = null;

	/**
	 * Human friendly name for the transport. ie the account name for smtp etc
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $match_type = 'exact';

	/**
	 * @var string
	 */
	protected $match_pattern = '';

	/**
	 * The type of transport
	 *
	 * @var string
	 */
	protected $transport_type;

	/**
	 * Options for the transport
	 *
	 */
	protected $transport_options = array();

	/**
	 * The type of transport
	 *
	 * @var string
	 */
	protected $backup_transport_type = '';

	/**
	 * Options for the transport
	 *
	 */
	protected $backup_transport_options = array();

	/**
	 * @var int
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

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
				if (Strings::utf8_strtolower($this->match_pattern) == $domain) {
					return true;
				}
				break;

			case self::MATCH_TYPE_EXACT:
				if (Strings::utf8_strtolower($this->match_pattern) == $from_address) {
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



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\EmailTransport';
		$metadata->setPrimaryTable(array( 'name' => 'email_transports', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'match_type', 'type' => 'string', 'length' => 15, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'match_type', ));
		$metadata->mapField(array( 'fieldName' => 'match_pattern', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'match_pattern', ));
		$metadata->mapField(array( 'fieldName' => 'transport_type', 'type' => 'string', 'length' => 80, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'transport_type', ));
		$metadata->mapField(array( 'fieldName' => 'transport_options', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'transport_options', ));
		$metadata->mapField(array( 'fieldName' => 'backup_transport_type', 'type' => 'string', 'length' => 80, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'backup_transport_type', ));
		$metadata->mapField(array( 'fieldName' => 'backup_transport_options', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'backup_transport_options', ));
		$metadata->mapField(array( 'fieldName' => 'run_order', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'run_order', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
