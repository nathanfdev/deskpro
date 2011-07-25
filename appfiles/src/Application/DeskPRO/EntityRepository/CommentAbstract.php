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
use Application\DeskPRO\ORM\QueryPartial;

use Doctrine\ORM\EntityRepository;

class CommentAbstract extends EntityRepository
{
	const FIELD = '';

	/**
	 * Get an array of all comments awaiting validation from each content type.
	 *
	 * @return array
	 */
	public static function getCombinedValidating($limit = 25, $order_dir = 'DESC')
	{
		$sql_parts = array();

		if (!is_array($limit)) {
			$limit = array(
				'max' => $limit,
				'offset' => 0
			);
		}

		$types = array(
			'article_comments'  => array('entity' => 'DeskPRO:ArticleComment', 'id_field' => 'article_id'),
			'download_comments' => array('entity' => 'DeskPRO:DownloadComment', 'id_field' => 'download_id'),
			'idea_comments'     => array('entity' => 'DeskPRO:IdeaComment', 'id_field' => 'idea_id'),
			'news_comments'     => array('entity' => 'DeskPRO:NewsComment', 'id_field' => 'news_id'),
		);

		#------------------------------
		# Fetch from each comment table with a union
		#------------------------------

		foreach ($types as $t => $t_info) {
			$sql_parts[] = "(
				SELECT id as comment_id, '$t' as content_type
				FROM $t
				WHERE status = 'validating'
			)";
		}

		$sql = implode(' UNION ', $sql_parts);
		$sql .= "ORDER BY id $order_dir LIMIT {$limit['offset']}, {$limit['max']}";

		$db = App::getDb();
		$results = $db->fetchAll($sql);

		if (!$results) return array();

		#------------------------------
		# Fetch each comment in the result
		#------------------------------

		$result_ids_typed = array();

		foreach ($results as $r) {
			if (!isset($result_typed[$r['content_type']])) {
				$result_typed[$r['content_type']] = array();
			}

			$result_typed[$r['content_type']][] = $r['comment_id'];
		}

		$results_typed = array();

		foreach ($result_ids_typed as $t => $ids) {
			$t_info = $types[$t];
			$results_typed[$t] = App::getEntityRepository($t_info['entity'])->getByIds($ids);
		}

		#------------------------------
		# Put back into original sort order
		# as a combined array
		#------------------------------

		$results_ordered = array();

		foreach ($results as $r) {
			if (isset($results_typed[$r['content_type']][$r['comment_id']])) {
				$results_ordered[] = $results_typed[$r['content_type']][$r['comment_id']];
			}
		}

		return $results_ordered;
	}

	public static function getCombinedValidatingCount()
	{
		$types = array(
			'article_comments',
			'download_comments',
			'idea_comments',
			'news_comments'
		);

		foreach ($types as $t) {
			$sql_parts[] = "(
				SELECT COUNT(*)
				FROM $t
				WHERE status = 'validating'
			)";
		}

		$sql = implode(' UNION ', $sql_parts);

		$db = App::getDb();
		$results = $db->fetchAllCol($sql);

		return array_sum($results);
	}

	public function getByIds(array $ids)
	{
		if (!$ids) return array();

		$ids = implode(',', $ids);

		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM " . $this->_entityName ." c INDEX BY c.id
			WHERE c.id IN ($ids)
		");
	}

	public function getComments($object)
	{
		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM " . $this->_entityName ." c
			WHERE c.status = ?1 AND c." . static::FIELD . " = ?2
			ORDER BY c.id DESC
		")->setParameter(1, 'visible')->setParameter(2, $object)->execute();
	}

	public function countAwaitingValidation()
	{
		$table = $this->getClassMetadata()->getTableName();
		return App::getDb()->fetchColumn("
			SELECT COUNT(*)
			FROM $table
			WHERE status = ?
		", array('validating'));
	}

	public function getValidatingComments()
	{
		return $this->getEntityManager()->createQuery("
			SELECT c
			FROM " . $this->_entityName ." c
			LEFT JOIN c.person p
			WHERE c.status = ?1
			ORDER BY c.id DESC
		")->setParameter(1, 'validating')->execute();
	}
}