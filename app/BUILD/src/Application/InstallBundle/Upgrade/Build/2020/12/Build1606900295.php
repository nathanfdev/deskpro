<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1606900295 extends AbstractBuild implements BlockingBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE guides ADD use_volumes TINYINT(1) NOT NULL');
    }

    public function run()
    {
        $db = $this->getDbConnection('default');

        $guides = $db->fetchAll('SELECT COUNT(t.id) as chapters, g.id FROM guides g LEFT JOIN topics t ON g.id = t.guide_id AND t.status = \'published\' AND t.no_content = 1 AND t.parent_id IS NOT NULL GROUP BY g.id');

        $statement = $db->prepare("INSERT INTO topics (guide_id, slug, person_id, title, content, content_input, content_input_type, status, no_content, display_order, date_created, date_updated, date_published) SELECT guide_id, :slug, person_id, title, content, content_input, content_input_type, status, '1', display_order, date_created, date_updated, date_published FROM topics WHERE id= :topic_id");
        foreach ($guides as $guide) {
            $pagesUnderVolumes = $db->fetchAll('SELECT p.id, p.slug FROM topics t INNER JOIN topics p ON p.id = t.parent_id AND p.parent_id IS NULL WHERE t.guide_id = ? AND t.status = \'published\' AND t.no_content = 0 GROUP BY p.id', [$guide['id']]);
            if ($guide['chapters'] > 0) {
                $db->update('guides', [
                    'use_volumes' => '1',
                ], ['id' => $guide['id']]);

                foreach ($pagesUnderVolumes as $page) {
                    $newSlug = ''.$page['slug'].'-volume';
                    $i       = 1;
                    while ($this->generateSlug($db, $newSlug) === true) {
                        $newSlug = ''.$page['slug'].'-volume'.$i++;
                    }

                    $statement->execute([
                       'slug'     => $newSlug,
                       'topic_id' => $page['id'],
                    ]);
                    $newVolumeId = $db->lastInsertId();
                    $db->executeUpdate("UPDATE topics SET parent_id = :parent_id, no_content=1 WHERE id= :topic_id", ['parent_id' => $newVolumeId, 'topic_id' => $page['id']]);
                }
            }
        }
        // Set all non root pages to have content on guides without volumes
        $db->query('UPDATE topics t INNER JOIN guides g ON g.id = t.guide_id SET t.no_content = 0 WHERE g.use_volumes = 0 AND t.parent_id <> 0');
    }

    public function generateSlug($db, $slug)
    {
        $slugExists = $db->fetchAll("SELECT topics.id FROM topics WHERE `slug` = '?'", [$slug]);

        return count($slugExists) > 0;
    }
}
