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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Util\Util;

class Comments
{
	protected $entity;
	protected $page_url;
	protected $adapter;

	/**
	 * Creates a new instance of this comments helper using the adapter
	 * defined in settings. Or returns null if no adapter is set
	 *
	 * @param  $page_url
	 * @param  $entity
	 * @return Comments
	 */
	public static function create($entity)
	{
		if (App::getSetting('core.comments_adapter') == 'disqus') {
			$adapter = 'DisqusComments';
		} elseif (App::getSetting('core.comments_adapter') == 'facebook') {
			$adapter = 'FacebookComments';
		} else {
			return null;
		}

		return new self($adapter, $entity);
	}

	/**
	 * @param string $adapter  A comment adapter, or a string of an base adapter classname
	 * @param string $page_url The permalink to the page
	 * @param Entity $entity   The entity we're adding comments to
	 */
	public function __construct($adapter, $entity)
	{
		$this->entity = $entity;

		if (is_string($adapter)) {
			$class = 'Application\\UserBundle\\Controller\\Helper\\CommentsAdapter\\' . $adapter;
			$adapter = new $class($entity);
		}

		$this->adapter = $adapter;
	}



	/**
	 * Get the HTML block from the adapter
	 *
	 * @return string
	 */
	public function getHtml()
	{
		return $this->adapter->getHtml();
	}
}
