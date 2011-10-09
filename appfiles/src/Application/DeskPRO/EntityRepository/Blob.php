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

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class Blob extends EntityRepository
{
	/**
	 * Get a blob by a combined ID/authcode
	 *
	 * @returb \Application\DeskPRO\Entity\Blob
	 */
	public function getByAuthId($auth_id)
	{
		if (strpos($auth_id, '-') === false) {
			return null;
		}

		list($blob_id, ) = explode('-', $auth_id, 2);
		$blob = App::findEntity('DeskPRO:Blob', $blob_id);
		if ($blob && $blob->getAuthId() != $auth_id) {
			$blob = null;
		}

		return $blob;
	}
}
