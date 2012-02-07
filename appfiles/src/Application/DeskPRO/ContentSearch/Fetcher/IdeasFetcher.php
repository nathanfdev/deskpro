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

namespace Application\DeskPRO\ContentSearch\Fetcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

use Orb\Util\Strings;

class IdeasFetcher extends AbstractFetcher
{
	const TYPENAME = 'feedback';

	/**
	 * Returns an array of entities identified by $related_ids, that the user is able to see.
	 *
	 * @param array $related_ids
	 * @return array
	 */
	function getEntities(array $related_ids)
	{
		return App::getEntityRepository('DeskPRO:Idea')->findById($related_ids, $this->person);
	}
}
