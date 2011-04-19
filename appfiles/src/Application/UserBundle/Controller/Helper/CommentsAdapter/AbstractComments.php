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

namespace Application\UserBundle\Controller\Helper\CommentsAdapter;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;
use \Orb\Util\Util;

abstract class AbstractComments
{
	protected $entity;
	protected $page_id;
	protected $page_url;

	/**
	 * @param string $page_url The permalink to the page
	 * @param Entity $entity   The entity we're adding comments to
	 */
	public function __construct($entity)
	{
		$this->page_url = $entity->getPermalink();
		$this->entity = $entity;
		$this->page_id  = 'dp_' . App::getSetting('core.site_id') . '_' . md5(get_class($entity)) . '_' . $entity->getId();

		$this->init();
	}

	protected function init() {}

	/**
	 * Get the HTML block for disqus templates
	 *
	 * @return string
	 */
	abstract public function getHtml();
}