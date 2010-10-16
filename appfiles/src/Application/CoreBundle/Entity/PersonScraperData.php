<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entity
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

/**
 * PersonScraperData stores the data we got from a PersonScraper.
 * This should be individual pieces of data, processed and normalized
 * for use in the system.
 *
 * For example, if the remote system has three fields
 * for "firstname middlename lastname", it might be normalized into a single
 * PersonScraperData as "full_name".
 *
 * This data is meant to be displayed in the various interfaces to show info about a user.
 *
 * PersonScraperData aren't directly tracked back to an exact piece of data in a remote
 * database. If you want the raw data, look in the PersonScraperAssociation.
 *
 * @Entity
 * @Table(name="person_scraper_data")
 */
class PersonScraperData extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var Application\CoreBundle\Entity\Person
	 * @ManyToOne(targetEntity="Person", inversedBy="emails")
	 * @JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * The usersource ID
	 * @var int
	 * @Column(name="usersource_id", type="integer")
	 */
	protected $person_id = null;

	/**
	 * @var Application\CoreBundle\Entity\PersonScraperAssoc
	 * @ManyToOne(targetEntity="PersonScraperAssoc", inversedBy="data")
	 * @JoinColumn(name="person_scraper_assoc_id", referencedColumnName="id")
	 */
	protected $person_scraper_assoc;

	/**
	 * @var int
	 * @Column(name="person_scraper_assoc_id", type="integer")
	 */
	protected $person_scraper_assoc_id;

	/**
	 * The groupname of this data. The interfaces can be customized, so for example, if an admin
	 * wanted to hide all "website_url" entries by default, we use the groupname to tag
	 * a piece of data as website_url so we can identify what it is.
	 *
	 * This is also used in mapping.
	 *
	 * @var string
	 * @Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname = null;

	/**
	 * The string value of this field we can display in interfaces.
	 *
	 * @var string
	 * @Column(name="value_text", type="text")
	 */
	protected $value_text;

	/**
	 * A HTML value we can use in the HTML web interfaces.
	 *
	 * @var string
	 * @Column(name="value", type="text", nullable=true)
	 */
	protected $value_html = null;

	/**
	 * The raw data that built up this data. This is usually one or more pieces of raw
	 * data fetcehd directly from the source. This data is may also be used in mapping
	 * to local fields.
	 *
	 * @var array
	 * @Column(name="raw_data", type="array", nullable=true)
	 */
	protected $raw_data = bull;
}
