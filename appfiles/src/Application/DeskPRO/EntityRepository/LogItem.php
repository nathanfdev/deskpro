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

class LogItem extends EntityRepository
{
	public function findBySn($log_sn)
	{
		try {
			return $this->_em->createQuery("
				SELECT l
				FROM DeskPRO:LogItem l
				WHERE l.session_name = ?1
				ORDER BY l.id DESC
			")->setParameter(1, $log_sn)
			  ->setMaxResults(1)
			  ->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}


	/**
	 * Count all error log items
	 *
	 * @return int
	 */
	public function getErrorLogsCount()
	{
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM log_items
			WHERE log_name = ?
		", array('error_log'));
	}


	
	/**
	 * Get an array of error logs
	 *
	 * @param int $page
	 * @param int $per_page
	 * @return array
	 */
	public function getErrorLogs($page = 1, $per_page = 25)
	{
		$offset = max(0, $page - 1);

		return $this->_em->createQuery("
			SELECT l
			FROM DeskPRO:LogItem l
			WHERE l.log_name = ?1
			ORDER BY l.id DESC
		")->setParameter(1, 'error_log')
		  ->setFirstResult($page)
		  ->setMaxResults($per_page)
		  ->execute();
	}
	


	/**
	 * Deletes all error logs
	 * 
	 * @return bool
	 */
	public function clearAllErrorLogs()
	{
		App::getDb()->executeUpdate("DELETE FROM log_items WHERE log_name = ?", array('error_log'));
		return true;
	}
}