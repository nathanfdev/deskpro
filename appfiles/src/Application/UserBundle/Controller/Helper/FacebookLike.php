<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller\Helper;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;
use \Orb\Util\Util;

class FacebookLike
{
	protected $entity;

	/**
	 * Creates a new instance of this helper
	 *
	 * @param  $page_url
	 * @param  $entity
	 * @return Comments
	 */
	public static function create($entity)
	{
		return new self($entity);
	}

	/**
	 * @param Entity $entity   The entity we're adding comments to
	 */
	public function __construct($entity)
	{
		$this->entity = $entity;
	}



	/**
	 * Get the HTML block from the adapter
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$html = App::get('templating')->render('UserBundle:Common:facebook-like.html.twig', array(
			'entity'           => $this->entity,
			'entity_type'      => get_class($this->entity),
			'entity_basetype'  => Util::getBaseClassname($this->entity),

			'page_permalink'   => $this->entity->getPermalink()
		));

		return $html;
	}
}