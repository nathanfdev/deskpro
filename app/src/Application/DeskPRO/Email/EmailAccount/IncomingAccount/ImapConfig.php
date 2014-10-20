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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

class ImapConfig implements AccountConfigInterface
{
	/**
	 * @var string
	 */
	public $host;

	/**
	 * Pop3 default is 143, secure 993
	 * @var int
	 */
	public $port = 143;

	/**
	 * @var string
	 */
	public $user;

	/**
	 * @var string
	 */
	public $password;

	/**
	 * 'ssl' or 'tls'
	 * @var null|string
	 */
	public $secure_mode = null;

	/**
	 * disable certificate validation (validate by default)
	 * @var bool
	 */
	public $no_validation = false;

	/**
	 * 'read', 'delete', 'archive'
	 * @var string
	 */
	public $mode = 'read';

	/**
	 * The mailbox to read from. Default blank means inbox.
	 * @var string
	 */
	public $read_mailbox = null;

	/**
	 * If using the 'archive' method, this is the mailbox name.
	 * @var string
	 */
	public $archive_mailbox = null;


	/**
	 * {@inheritDoc}
	 */
	public function serializeJsonArray()
	{
		return array(
			'host'            => $this->host,
			'port'            => $this->port,
			'user'            => $this->user,
			'password'        => $this->password,
			'secure_mode'     => $this->secure_mode,
			'no_validation'   => $this->no_validation,
			'mode'            => $this->mode,
			'read_mailbox'    => $this->read_mailbox,
			'archive_mailbox' => $this->archive_mailbox
		);
	}


	/**
	 * {@inheritDoc}
	 */
	public static function unserializeJsonArray(array $data)
	{
		$obj = new self();
		foreach ($data as $k => $v) {
			$obj->$k = $v;
		}

		return $obj;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getType()
	{
		return 'imap';
	}


	############################################################################
	# Validation Metadata
	############################################################################

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('host', new Constraints\NotBlank());
		$metadata->addPropertyConstraint('port', new Constraints\GreaterThan(array('value' => 1)));
		$metadata->addPropertyConstraint('secure_mode', new Constraints\Choice(array(
			'choices' => array('none', 'ssl', 'tls')
		)));
		$metadata->addPropertyConstraint('mode', new Constraints\Choice(array(
			'choices' => array('read', 'delete', 'archive')
		)));
	}
}