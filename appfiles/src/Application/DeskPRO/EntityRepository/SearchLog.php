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
use Doctrine\ORM\EntityRepository;

use Orb\Util\Arrays;

class SearchLog extends EntityRepository
{
	public function getRatedSearchesFor($object_type, $object_id, $structure = 'all')
	{
		$search_ids_to_rating = APp::getDb()->fetchAllKeyValue("
			SELECT searchlog_id, rating
			FROM ratings
			WHERE object_type = ? AND object_id = ? AND searchlog_id IS NOT NULL
		", array($object_type, $object_id));

		$logs = $this->getByIds(array_keys($search_ids_to_rating));

		if (!$logs) {
			return array();
		}

		if ($structure == 'all') {
			return $logs;
		}

		if ($structure == 'grouped') {
			$ret = array('helpful' => array(), 'unhelpful' => array());
			foreach ($logs as $l) {
				if ($search_ids_to_rating[$l['id']] >= 1) {
					$ret['helpful'] = $l;
				} else {
					$ret['unhelpful'] = $l;
				}
			}

			return $ret;

		} elseif ($structure == 'counted') {

			$ret = array('helpful' => array(), 'unhelpful' => array());
			foreach ($logs as $l) {

				if ($search_ids_to_rating[$l['id']] >= 1) {
					if (!isset($ret['helpful'][$l['query']])) {
						$ret['helpful'][$l['query']] = 0;
					}
					$ret['helpful'][$l['query']]++;
				} else {
					if (!isset($ret['unhelpful'][$l['query']])) {
						$ret['unhelpful'][$l['query']] = 0;
					}
					$ret['unhelpful'][$l['query']]++;
				}
			}

			asort($ret['helpful'], \SORT_NUMERIC);
			asort($ret['unhelpful'], \SORT_NUMERIC);

			return $ret;
		}

		return null;
	}

	public function getByIds(array $ids)
	{
		$ids = Arrays::castToType($ids, 'int');
		if (!$ids) {
			return array();
		}

		return $this->getEntityManager()->createQuery("
			SELECT l
			FROM DeskPRO:SearchLog l
			WHERE l.id IN (" . implode(',', $ids) . ")
			ORDER BY l.id DESC
		")->execute();
	}
}
