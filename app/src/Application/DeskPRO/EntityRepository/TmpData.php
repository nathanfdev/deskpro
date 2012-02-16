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

use Application\DeskPRO\Entity\TmpData as TmpDataEntity;

use Doctrine\ORM\EntityRepository;

class TmpData extends EntityRepository
{
	public function getByCode($code, $type = null)
	{
		$info = TmpDataEntity::getPartsFromCode($code);
		if (!$info) return null;

		$tmpdata = $this->find($info['id']);
		if ($tmpdata['auth'] != $info['auth']) return null;

		if ($type AND $tmpdata->getType() != $type) return null;

		return $tmpdata;
	}
}