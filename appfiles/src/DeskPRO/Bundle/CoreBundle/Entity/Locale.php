<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Bundle\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Locales that users can choose.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="locales", indexes={@Index(name="locale_idx", columns={"locale"})})
 */
class Locale extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * The locale
	 *
	 * @var int
	 * @Column(name="locale", type="string", length=5)
	 */
	protected $locale;


	/**
	 * @var int
	 * @Column(name="language_id", type="integer")
	 */
	protected $language_id;


	/**
	 * The language this locale uses
	 *
	 * @var Style
	 * @OneToOne(targetEntity="Language")
	 * @JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language;
}