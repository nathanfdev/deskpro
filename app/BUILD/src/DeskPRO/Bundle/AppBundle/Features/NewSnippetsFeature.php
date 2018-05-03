<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewSnippetsFeature.
 */
class NewSnippetsFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'new_snippets';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'New snippets';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'New snippets interface for tickets and chats.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
New Snippets replaces the current interface in chat and tickets with a new and improved version more modern and more 
reactive.<br/><br/>
Installing New Snippets will copy your current snippets over to the new system, snippets categories will be replaced
by snippets labels allowing you to assign several labels to the same snippet.

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling New Snippets will revert your helpdesk back to using the previous snippet system.<br/><br/>
Edits to the snippets and snippets added will be <string>lost</string> forever 
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailability()
    {
        return [self::AVAILABLE_EVERYWHERE];
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabledOnInstall()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeEnable(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.default_entity_manager');
        $this->copySnippets($em);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDisable(ContainerInterface $container)
    {
        $em         = $container->get('doctrine.orm.default_entity_manager');
        $connection = $em->getConnection();

        $em->beginTransaction();
        try {
            $queries[] = 'SET FOREIGN_KEY_CHECKS = 0;';
            $queries[] = 'TRUNCATE TABLE snippet_visible_departments;';
            $queries[] = 'TRUNCATE TABLE snippet_translation_blob;';
            $queries[] = 'TRUNCATE TABLE snippet_ownership_teams;';
            $queries[] = 'TRUNCATE TABLE snippet_labels;';
            $queries[] = 'TRUNCATE TABLE snippet_translations;';
            $queries[] = 'TRUNCATE TABLE snippets;';
            $queries[] = 'SET FOREIGN_KEY_CHECKS = 1;';

            foreach ($queries as $query) {
                $connection->query($query);
            }
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }
    }

    /**
     * @param EntityManager $em
     *
     * @throws \Exception
     */
    private function copySnippets(EntityManager $em)
    {
        $connection = $em->getConnection();

        $langId = $connection->fetchColumn('SELECT value FROM settings WHERE name= \'core.default_language_id\'') ?: 1;

        $em->beginTransaction();
        try {
            $connection->query(
                "INSERT INTO snippets
                      (id, person_id, shortcut_code, title, types, is_draft, ownership_global, visible_global, usage_count, positive_ratings, neutral_ratings, negative_ratings, is_split, date_created) 
                      SELECT
                          ts.id,
                          IFNULL(ts.person_id, tcs.person_id) as person_id,
                          ts.shortcut_code,
                          ol_title.value as title,
                          REPLACE('ticket', 'tickets', tcs.typename) as types,
                          ts.is_draft,
                          tcs.is_global as ownership_global,
                          1 as visible_global,
                          0 as usage_count,
                          0 as positive_ratings,
                          0 as neutral_ratings,
                          0 as negative_ratings,
                          0 as is_split,
                          NOW()
                    FROM text_snippets ts
                    LEFT JOIN object_lang ol_title ON ol_title.ref = CONCAT('text_snippets.', ts.id) AND ol_title.prop_name = 'title'
                    LEFT JOIN object_lang ol_content ON ol_content.ref = CONCAT('text_snippets.', ts.id) AND ol_content.prop_name = 'snippet'
                    LEFT JOIN text_snippet_categories tcs ON ts.category_id = tcs.id
                    WHERE ol_content.id IS NOT NULL
                    GROUP BY ts.id, ol_title.id;");
            $connection->query(
                "INSERT INTO snippet_labels
                      (
                        snippet_id,
                        label
                      ) 
                      SELECT 
                        ts.id, 
                        ol_category.value
                    FROM text_snippet_categories tsc
                      LEFT JOIN text_snippets ts ON tsc.id = ts.category_id
                      LEFT JOIN object_lang ol_category ON ol_category.ref = CONCAT('text_snippet_categories.', tsc.id) AND ol_category.language_id = $langId
                      LEFT JOIN object_lang ol_content ON ol_content.ref = CONCAT('text_snippets.', ts.id) AND ol_content.prop_name = 'snippet'
                    WHERE ol_content.id IS NOT NULL
                    AND ts.id IS NOT NULL
                    GROUP BY ts.id, ol_category.id");
            $connection->query(
                'INSERT INTO snippet_translations
                    (
                      snippet_id, 
                      language_id, 
                      content, 
                      title
                    ) 
                    SELECT
                      ts.id as snippet_id,
                      ol_title.language_id,
                      ol_content.value as content,
                      ol_title.value as title
                    FROM text_snippets ts
                      LEFT JOIN object_lang ol_title ON ol_title.ref = CONCAT(\'text_snippets.\', ts.id) AND ol_title.prop_name = \'title\'
                      LEFT JOIN object_lang ol_content ON ol_content.ref = CONCAT(\'text_snippets.\', ts.id) AND ol_content.prop_name = \'snippet\' AND ol_content.language_id = ol_title.language_id
                    WHERE ol_content.value <> \'\'');
            // Set the auto_increment to the minimum value it needs
            $connection->query('ALTER TABLE snippets AUTO_INCREMENT = 1');
            $connection->query('DROP TABLE IF EXISTS perms');
            $connection->query('CREATE TEMPORARY TABLE perms (`name` VARCHAR (255)) COLLATE utf8_general_ci');
            $connection->query('INSERT INTO perms (`name`) VALUES (\'agent_snippets.edit_by_others\'), (\'agent_snippets.delete_by_others\'), (\'agent_snippets.create_snippet\')');
            $connection->query(
                'INSERT INTO permissions
                    (`usergroup_id`, `name`, `value`, `is_active`)
                    SELECT u.id, p.name, 1, 1
                    FROM usergroups u
                      CROSS JOIN perms p
                      LEFT JOIN permissions pe ON pe.usergroup_id = u.id AND pe.name = p.name
                    WHERE is_agent_group = 1 AND sys_name IS NULL AND pe.id IS NULL');
            $connection->query('DROP TABLE perms');
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }
    }
}
