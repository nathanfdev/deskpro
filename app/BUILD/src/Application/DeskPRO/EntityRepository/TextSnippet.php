<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Component\Util\ListUtils;
use Doctrine\DBAL\Query\QueryBuilder;
use Orb\Util\Arrays;

class TextSnippet extends AbstractEntityRepository
{
    /**
     * Get snippets for agent grouped by category (@see groupSnippetCollection).
     *
     * @param $typename
     * @param PersonEntity $agent
     *
     * @return array
     */
    public function getSnippetsForAgent($typename, PersonEntity $agent)
    {
        $agent->loadHelper('AgentTeam');

        $dql = '
            SELECT s, c
            FROM DeskPRO:TextSnippet s
            INNER JOIN s.category c
            WHERE
                c.typename = ?1
                AND (c.person = ?2 OR c.is_global = true)
        ';

        $coll = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $typename)
            ->setParameter(2, $agent)
            ->execute();

        if (!$coll) {
            return array();
        }

        return $this->groupSnippetCollection($coll);
    }

    /**
     * Get all snippets for an agent with limits.
     *
     * @param $typename
     * @param PersonEntity $agent
     * @param int          $page
     * @param int          $per_page
     * @param int          $in_category
     *
     * @return mixed
     */
    public function getAllSnippetsForAgent($typename, PersonEntity $agent, $page = 1, $per_page = 250, $in_category = null)
    {
        $dql = '
            SELECT s, c
            FROM DeskPRO:TextSnippet s
            INNER JOIN s.category c
            WHERE
                c.typename = ?1
                AND (c.person = ?2 OR c.is_global = true)
        ';

        if ($in_category) {
            $dql .= ' AND c = ?3 ';
        }

        $q = $this->getEntityManager()->createQuery($dql)
            ->setMaxResults($per_page)
            ->setFirstResult(($page - 1) * $per_page)
            ->setParameter(1, $typename)
            ->setParameter(2, $agent);

        if ($in_category) {
            $q->setParameter(3, $in_category);
        }

        $coll = $q->execute();

        return $coll;
    }

    public function filterSnippetsForAgent($search, $typename, PersonEntity $agent = null, $page = 1, $perPage = 250, $filterCatIds = null, $filterLangIds = null)
    {
        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();

        if ($filterLangIds) {
            $filterLangIds = (array) $filterLangIds;
            $filterLangIds = array_map('intval', $filterLangIds);
            $filterLangIds = Arrays::removeFalsey($filterLangIds);
            if (!$filterLangIds) {
                // bad input, should match nothing
                $filterLangIds = [0];
            }
        }

        if ($filterCatIds) {
            $filterCatIds = (array) $filterCatIds;
            $filterCatIds = array_map('intval', $filterCatIds);
            $filterCatIds = Arrays::removeFalsey($filterCatIds);
            if (!$filterCatIds) {
                // bad input, should match nothing
                $filterCatIds = [0];
            }
        }

        if ($search) {
            if (strlen($search) < 3) {
                return [];
            }

            $qb = new QueryBuilder($conn);
            $qb->select('DISTINCT(object_lang.ref_id)')
               ->from('object_lang')
               ->leftJoin('object_lang', 'text_snippets', 'ts', 'ts.id = object_lang.ref_id')
               ->leftJoin('ts', 'text_snippet_categories', 'cat', 'cat.id = ts.category_id')
               ->andWhere('cat.typename = :type')->setParameter('type', $typename);

            if ($agent) {
                $qb->andWhere('(cat.is_global OR cat.person_id = :personId)')->setParameter(':personId', $agent->getId());
            }

            if ($filterLangIds) {
                $qb->andWhere('object_lang.language_id IN (:langIds)')
                   ->setParameter(':langIds', (array) $filterLangIds, Connection::PARAM_INT_ARRAY);
            }

            $qb->andWhere('object_lang.ref_type = "text_snippets"')
               ->andWhere('object_lang.value LIKE :search')->setParameter(':search', '%'.addcslashes($search, '%_\\').'%')
               ->setMaxResults(250);

            $matchingIds = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($matchingIds)) {
                return [];
            }
        } else {
            $matchingIds = null;
        }

        $qb = new QueryBuilder($conn);
        $qb->select('text_snippets.*')
           ->from('text_snippets')
           ->leftJoin('text_snippets', 'text_snippet_categories', 'cat', 'cat.id = text_snippets.category_id')
           ->andWhere('cat.typename = :type')->setParameter('type', $typename);

        if ($agent) {
            $qb->andWhere('(cat.is_global OR cat.person_id = :personId)')->setParameter(':personId', $agent->getId());
        }

        if ($filterCatIds) {
            $qb->setParameter('text_snippets.category_id IN (:catIds)', (array) $filterCatIds, Connection::PARAM_INT_ARRAY);
        }
        if ($filterLangIds) {
            $qb->innerJoin('text_snippets', 'object_lang', 'l', '(l.ref_id = text_snippets.id AND l.ref_type = "text_snippets" AND l.language_id IN (:langIds))')
               ->setParameter(':langIds', $filterLangIds, Connection::PARAM_INT_ARRAY);
        }
        if ($matchingIds) {
            $qb->andWhere('text_snippets.id IN (:ids)')->setParameter(':ids', $matchingIds, Connection::PARAM_INT_ARRAY);
        }

        $qb->setMaxResults($perPage);
        $qb->setFirstResult(($page - 1) * $perPage);

        $snippets = $qb->execute()->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($snippets)) {
            return [];
        }

        $snippetIds = ListUtils::map($snippets, function ($s) { return $s['id']; });

        $langData = $conn->fetchAll("
            SELECT language_id, ref_id, prop_name, value
            FROM object_lang
            WHERE ref_type = 'text_snippets' AND ref_id IN (?)
        ", [$snippetIds], [Connection::PARAM_INT_ARRAY]);

        $langDataMap = [];
        foreach ($langData as $l) {
            if (!isset($langDataMap[$l['ref_id']])) {
                $langDataMap[$l['ref_id']] = [];
            }
            if (!isset($langDataMap[$l['ref_id']][$l['language_id']])) {
                $langDataMap[$l['ref_id']][$l['language_id']] = ['title' => '', 'snippet' => ''];
            }
            $langDataMap[$l['ref_id']][$l['language_id']][$l['prop_name']] = $l['value'];
        }

        $langLocales = array();
        foreach (App::getContainer()->getLanguageData()->getAll() as $lang) {
            $langLocales[$lang->getId()] = $lang->getLocale();
        }

        $res = array();
        foreach ($snippets as $snippet) {
            if (isset($langDataMap[$snippet['id']])) {
                continue;
            }

            $translation = $langDataMap[$snippet['id']];

            $data = array(
                'id'            => $snippet['id'],
                'shortcut_code' => $snippet['shortcut_code'],
                'is_draft'      => (bool) $snippet['is_draft'],
                'category_id'   => $snippet['category_id'],
                'title'         => array(),
                'snippet'       => array(),
            );

            foreach ($translation as $langId => $values) {
                if (empty($langLocales[$langId])) {
                    // old records from a deleted lang
                    continue;
                }
                $data['title'][] = array(
                    'language_id' => $langId,
                    'locale'      => $langLocales[$langId],
                    'value'       => $values['title'],
                );
                $data['snippet'][] = array(
                    'language_id' => $langId,
                    'locale'      => $langLocales[$langId],
                    'value'       => $values['snippet'],
                );
            }

            $res[] = $data;
        }

        return $res;
    }

    /**
     * Count all of an agents snippets.
     *
     * @param $typename
     * @param PersonEntity $agent
     *
     * @return mixed
     */
    public function countSnippetsForAgent($typename, PersonEntity $agent)
    {
        return App::getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM text_snippets
            INNER JOIN text_snippet_categories ON (text_snippet_categories.id = text_snippets.category_id)
            WHERE
                text_snippet_categories.typename = ?
                AND (text_snippets.person_id = ? OR text_snippet_categories.is_global = 1)
        ', array($typename, $agent->getId()));
    }

    /**
     * Group a collection of snippets.
     *
     * @param $collection
     *
     * @return array
     */
    public function groupSnippetCollection($collection)
    {
        $ret = array();

        foreach ($collection as $snippet) {
            if (!isset($ret[$snippet->category['id']])) {
                $ret[$snippet->category['id']] = array('category' => $snippet->category, 'snippets' => array());
            }

            $ret[$snippet->category['id']]['snippets'][] = $snippet;
        }

        return $ret;
    }
}
