<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Routing;

use Orb\Util\Strings;

class Router extends \Symfony\Bundle\FrameworkBundle\Routing\Router
{
	public function setOptions(array $options)
	{
		if (isset($options['debug'])) {
			$options['debug'] = false;
		}

		return parent::setOptions($options);
	}


	/**
	 * Read the ID in a slug: 123-some-title will return 123
	 *
	 * If no ID couldbe found, then 0 is returned.
	 *
	 * @param $slug
	 * @return int
	 */
	public function getIdFromSlug($slug)
	{
		$id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);

		if (!$id) {
			return 0;
		}

		return (int)$id;
	}
}
