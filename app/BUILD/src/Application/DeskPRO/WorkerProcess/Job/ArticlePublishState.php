<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\News;

/**
 * Goes through articles with a publish date that was set in the future (publish now),
 * or an end date set (deleting or archivng now).
 */
class ArticlePublishState extends AbstractJob
{
    const DEFAULT_INTERVAL = 1800; // 30 mins

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->processEntityClass(Article::class);
        $this->processEntityClass(News::class);
    }

    /**
     * @param string $entityClass
     */
    private function processEntityClass($entityClass)
    {
        $tableName = App::$container->getEm()->getClassMetadata($entityClass)->getTableName();

        // publish articles
        $publishIds = App::getDb()->fetchAllCol("
            SELECT id
            FROM $tableName
            WHERE hidden_status = 'unpublished'
            AND date_published < :current_date
            AND (date_end > :current_date OR date_end IS NULL)
        ", ['current_date' => date('Y-m-d H:i:s')]);

        $this->processEntities($publishIds, $entityClass, function ($article) {
            /* @var Article|News $article */
            $article->setStatus(Article::STATUS_PUBLISHED);
        });

        // unpublish articles
        $unpublishIds = App::getDb()->fetchAllCol("
            SELECT id
            FROM $tableName
            WHERE status = 'published'
            AND date_end < ?
        ", [date('Y-m-d H:i:s')]);

        $this->processEntities($unpublishIds, $entityClass, function ($article) {
            /** @var Article|News $article */
            if ($article->getEndAction() === Article::END_ACTION_ARCHIVE) {
                $article->setStatus(Article::STATUS_ARCHIVED);
            } else {
                $article->setStatus(Article::STATUS_HIDDEN.'.'.Article::HIDDEN_STATUS_UNPUBLISHED);
            }
        });

        // log results
        if ($publishIds || $unpublishIds) {
            $part = [];
            if ($publishIds) {
                $part[] = sprintf('Published %s %s', count($publishIds), $tableName);
            }
            if ($unpublishIds) {
                $part[] = sprintf('Unpublished %s %s', count($unpublishIds), $tableName);
            }

            $msg = implode(' and ', $part);
            $this->logStatus($msg);
        }
    }

    /**
     * @param array    $ids
     * @param string   $entityClass
     * @param callable $handler
     */
    private function processEntities(array $ids, $entityClass, callable $handler)
    {
        $em = App::$container->getEm();

        foreach (array_chunk($ids, 20) as $batchIds) {
            $articles = $em->getRepository($entityClass)->findBy(['id' => $batchIds]);
            foreach ($articles as $article) {
                $handler($article);
                $em->persist($article);
            }

            $em->flush();
            $em->clear();
        }
    }
}
