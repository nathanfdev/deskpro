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

use Application\DeskPRO\App;

/**
 * Raw email sources
 *
 * @orm:Entity
 * @orm:Table(name="email_sources",
 *     indexes={@orm:Index(name="object_idx", columns={"object_type", "object_id"})}
 * )
 */
class EmailSource extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\EmailGateway
	 * @orm:ManyToOne(targetEntity="EmailGateway")
	 * @orm:JoinColumn(name="gateway_id", referencedColumnName="id")
	 */
	protected $gateway = null;

	/**
	 * The type of object this is attached to (should be the table name of
	 * the super type, eg: tickets, people, organizations).
	 *
	 * This typically is not set until after the email is processed (ie
	 * the status is 'processed').
	 *
	 * @var string
	 * @orm:Column(name="object_type", type="string", length=50)
	 */
	protected $object_type = '';

	/**
	 * The ID of the object this is attached to.
	 *
	 * @var int
	 * @orm:Column(name="object_id", type="integer")
	 */
	protected $object_id = '';

	/**
	 * Just the headers portion of the email
	 *
	 * @var string
	 * @orm:Column(name="headers", type="string", length=1000)
	 */
	protected $headers;

	/**
	 * The current status of the message:
	 * - inserted: Only inserted
	 * - complete: Fully processed
	 *
	 * @var string
	 * @orm:Column(name="status", type="string", length=15)
	 */
	protected $status = 'inserted';

	/**
	 * The path to the raw email if it was saved to the filesystem
	 *
	 * @var string
	 * @orm:Column(name="save_path", type="string", length=255)
	 */
	protected $save_path = '';

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * The raw source, pieced together.
	 *
	 * This is public on purpose. The AbstractFetcher
	 * sets this property for efficiency in cases where an email
	 * may be processed immediately after being read, we dont
	 * re-fetch the data from the db.
	 *
	 * @var string
	 */
	public $_raw = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}



	/**
	 * Get the full raw source of the email
	 *
	 * @return string
	 */
	public function getRawSource()
	{
		if ($this->_raw !== null) return $this->_raw;

		$parts = array();
		$statement = App::getDb()->executeQuery("SELECT data FROM email_sources_blobs WHERE source_id = ?", array($this->id));

		while ($row = $statement->fetch(\PDO::FETCH_NUM)) {
			$parts[] = $row[0];
		}

		$parts = implode('', $parts);
		$this->_raw = $parts;

		return $this->_raw;
	}
}
