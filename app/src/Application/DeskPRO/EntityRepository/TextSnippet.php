<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
            LEFT JOIN s.category c
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
            LEFT JOIN s.category c
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

    public function filterSnippetsForAgent($search, $typename, PersonEntity $agent, $page = 1, $per_page = 250, $in_category = null, $language_id = null)
    {
        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();

        // find proper categories
        if (!$in_category) {
            $dql = '
                SELECT PARTIAL c.{id}
                FROM DeskPRO:TextSnippetCategory c
                WHERE c.typename = :typename AND (c.person = :person OR c.person IS NULL)
            ';

            $q = $this->getEntityManager()->createQuery($dql)->setParameters(array(
                'typename' => $typename,
                'person'   => $agent,
            ));

            $in_category = array();
            foreach ($q->getArrayResult() as $row) {
                $in_category[] = $row['id'];
            };
        } else {
            $in_category = (array) $in_category;
        }

        // find proper translations
        $sql = sprintf('
            select l1.ref_id, l1.language_id, l1.value as title, l2.value as snippet
            from object_lang l1
            join object_lang l2 on
                l1.ref_type = "text_snippets"
                and l2.ref_type = "text_snippets"
                and l1.language_id = l2.language_id
                and l1.prop_name = "title"
                and l2.prop_name = "snippet"
                and l1.ref_id = l2.ref_id
            where l1.value like :title
            limit %d, %d
        ', --$page * $per_page, $per_page);
        $params = array('title' => '%'.$search.'%');
        if ($language_id) {
            $sql .= ' and language_id = :language_id';
            $params['language_id'] = $language_id;
        }
        $ids = array();
        $map = array();
        foreach ($conn->fetchAll($sql, $params) as $row) {
            $map[$row['ref_id']][$row['language_id']] = array(
                'title'   => $row['title'],
                'snippet' => $row['snippet'],
            );
            $ids[] = $row['ref_id'];
        }

        // find snippets
        $sql = '
            select * from text_snippets
            where category_id in (:categories) and id in (:ids)
        ';
        $params   = array('categories' => $in_category, 'ids' => $ids);
        $types    = array('categories' => Connection::PARAM_INT_ARRAY, 'ids' => Connection::PARAM_INT_ARRAY);
        $snippets = $conn->fetchAll($sql, $params, $types);

        $langs = array();
        foreach (App::getContainer()->getLanguageData()->getAll() as $lang) {
            $langs[$lang->getid()] = $lang->getLocale();
        }
        $res = array();
        foreach ($snippets as $snippet) {
            if (!$translation = @$map[$snippet['id']]) {
                continue;
            }

            $data = array(
                'id'            => $snippet['id'],
                'shortcut_code' => $snippet['shortcut_code'],
                'is_draft'      => (bool) $snippet['is_draft'],
                'category_id'   => $snippet['category_id'],
                'title'         => array(),
                'snippet'       => array(),
            );

            foreach ($translation as $lang_id => $values) {
                $data['title'][] = array(
                    'language_id' => $lang_id,
                    'locale'      => $langs[$lang_id],
                    'value'       => $values['title'],
                );
                $data['snippet'][] = array(
                    'language_id' => $lang_id,
                    'locale'      => $langs[$lang_id],
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
            LEFT JOIN text_snippet_categories ON (text_snippet_categories.id = text_snippets.category_id)
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
