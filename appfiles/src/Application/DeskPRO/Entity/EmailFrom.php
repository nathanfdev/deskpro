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

/**
 * A "from" email address, and how to send emails (SMTP, native etc) from it.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\EmailFrom")
 * @ORM_Mapping\Table(name="email_from")
 */
class EmailFrom extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The human readable "from" name
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="text", length=100)
	 */
	protected $name = '';

	/**
	 * The email address for the account.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="address", type="text", length=255)
	 */
	protected $address;

	/**
	 * The transport class. This can be a classname, or a callback function
	 * (eg static factory) that returns one.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="transport_class", type="string", length=80)
	 */
	protected $transport_class = '';

	/**
	 * Options for the transport
	 *
	 * @ORM_Mapping\Column(name="transport_options", type="array")
	 */
	protected $transport_options = array();

	/**
	 * @var \Swift_Transport
	 */
	protected $_transport = null;



	/**
	 * @return \Swift_Transport
	 */
	public function getTransport()
	{
		if ($this->_transport !== null) return $this->_transport;

		if (strpos($this->transport_class, '::')) {
			$this->_transport = call_user_func($this->transport_class, $this->transport_options);
		} else {
			$this->_transport = new $this->transport_class($this->transport_options);
		}

		return $this->_transport;
	}


	
	/**
	 * Creates a new instance of a known SwiftMailer transport.
	 *
	 * @static
	 * @param  $options
	 * @return \Swift_Transport
	 */
	public static function createTransportInstance($options)
	{
		$tr = null;
		$options['type'] = 'mail';
		switch ($options['type']) {
			case 'smtp':
				if (!$options['ssl']) $options['ssl'] = null;
				else $options['ssl'] = 'ssl';

				$tr = \Swift_SmtpTransport::newInstance($options['server'], $options['port'], $options['ssl']);

				if (!empty($options['username']) OR !empty($options['password'])) {
					$tr->setUsername($options['username']);
					$tr->setPassword($options['password']);
				}

				break;

			case 'sendmail':
				$tr = \Swift_SendmailTransport::newInstance("{$options['sendmail_path']} -bs");
				break;

			case 'mail':
				$tr = \Swift_MailTransport::newInstance();
				break;
		}

		return $tr;
	}
}