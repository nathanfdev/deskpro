<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use DeskPRO\Bundle\AppBundle\Settings\Model\Portal\KbSettings;

/**
 * Class ArticleAutoUnpublish.
 */
class ArticleAutoUnpublish extends AbstractJob
{
    const DEFAULT_INTERVAL = 1800; // 30 mins

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        /** @var KbSettings $kbSettings */
        $kbSettings = App::$container->get('portal_settings_resolver')->getKbSettings();
        if ($kbSettings->isAutoUnpublishReview() && $kbSettings->getAutoUnpublishReviewInterval()) {
            $interval = $kbSettings->getAutoUnpublishReviewInterval();
            $unit     = $kbSettings->getAutoUnpublishReviewUnit() ?: Article::REVIEW_DATE_UNIT_DAYS;
            $offset   = strtotime("+$interval $unit") - time();

            $autoUnpublishIds = App::getDb()->fetchAllCol("
            SELECT id
            FROM articles
            WHERE status = 'published'
            AND date_next_review IS NOT NULL AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date_next_review) > ?", [$offset]);

            $this->processEntities($autoUnpublishIds, function ($article) {
                /* @var Article $article */
                if ($article->getEndAction() === Article::END_ACTION_ARCHIVE) {
                    $article->setStatus(Article::STATUS_ARCHIVED);
                } else {
                    $article->setStatus(Article::STATUS_HIDDEN.'.'.Article::HIDDEN_STATUS_UNPUBLISHED);
                }
            });
        }
    }

    /**
     * @param array    $ids
     * @param callable $handler
     */
    private function processEntities(array $ids, callable $handler)
    {
        $em = App::$container->getEm();

        foreach (array_chunk($ids, 20) as $batchIds) {
            $articles = $em->getRepository(Article::class)->findBy(['id' => $batchIds]);
            foreach ($articles as $article) {
                $handler($article);
                $em->persist($article);
            }

            $em->flush();
            $em->clear();
        }
    }
}
