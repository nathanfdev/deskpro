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

use Doctrine\ORM\EntityRepository;
use Application\DeskPRO\App;

class Stat extends EntityRepository
{
	
	/**
	 * Get enabled stats
	 *
	 * @return array
	 */
	public function getEnabledStats()
	{
		$stats = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:Stat s
			WHERE s.disabled = :disabled
			ORDER BY s.starred DESC, s.title
		")->setParameter('disabled', false)->execute();

		return $stats;
	}
	
	/**
	 * Get the stats requiring updating
	 *
	 * @return array
	 */
	public function getStatsRequiringUpdate($time = null)
	{
		// Check a time is set, otherwise its now
		if (true === is_null($time)) {
			$time = time();
		}
		
		$stats = $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:Stat s
			WHERE s.disabled = :disabled
			ORDER BY s.starred DESC, s.title
		")->setParameter('disabled', false)->execute();

		return $stats;
	}
}