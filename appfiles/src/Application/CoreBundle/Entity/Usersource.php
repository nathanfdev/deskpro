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

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * This record defines the relationship between a Person and a Usersource.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="person_auth")
 */
class Usersource extends \DeskPRO\Domain\DomainObject
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
	 * A note or description about the user source (admin eyes)
	 *
	 * @var string
	 * @Column(name="note", type="text")
	 */
	protected $note = '';


	/**
	 * The title of this usersource. This SHOULD be a phrase ID so the title can change
	 * based on language.
	 *
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title = '';


	/**
	 * The description of this usersource. This SHOULD be a phrase ID so the title can change
	 * based on language.
	 *
	 * @var string
	 * @Column(name="description", type="string", length=255)
	 */
	protected $description = '';


	/**
	 * The URL/homepage of this service.
	 *
	 * @var string
	 * @Column(name="url", type="string", length=255)
	 */
	protected $url = '';


	/**
	 * Options we'll pass to the handlers
	 * 
	 * @var array
	 * @Column(name="handler_options", type="array")
	 */
	protected $handler_options = array();


	/**
	 * The PHP namespace for this handler.
	 *
	 * @var string
	 * @Column(name="handler_namepsace", type="string", length=255)
	 */
	protected $handler_namespace;

	

	/**
	 * Is this a source that requires periodic polling to check for remote updates?
	 *
	 * @var bool
	 * @Column(name="is_polling_source", type="boolean")
	 */
	protected $is_polling_source = false;


	/**
	 * The order in which to display this source
	 * @var int
	 * @Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;
}