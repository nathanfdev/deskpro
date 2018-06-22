<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\ContentRevision;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleRevision;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadRevision;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackRevision;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsRevision;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicRevision;
use GorHill\FineDiff\FineDiff;

class Util
{
    private function __construct()
    {
    }

    public static function findOrCreate($content, $edit_field, Person $person)
    {
        $entity = self::getRevisionClass($content, true);
        $field  = self::getContentField($content);

        $em = App::getOrm();

        $timesnip = date_create('-10 minutes');

        // For us to reuse a rev, it has to:
        // - be the latest one (ie no other revisions by other people)
        // - no more than an hour old
        // - and not a currently set field

        $rev = $em->createQuery("
            SELECT r
            FROM $entity r
            WHERE r.$field = ?1
            ORDER BY r.id DESC
        ")->setMaxResults(1)->setParameters([1 => $content])->getOneOrNullResult();

        $has_field = false;
        if ($rev) {
            foreach ((array) $edit_field as $f) {
                if ($rev[$f]) {
                    $has_field = true;
                } else {
                    $has_field = false;
                    break;
                }
            }
        }

        if (!$rev or $has_field or $rev->person['id'] != $person['id'] or $rev['date_created'] < $timesnip) {
            $rev_class   = self::getRevisionClass($content);
            $rev         = new $rev_class();
            $rev->person = $person;
            $rev->$field = $content;
        }

        return $rev;
    }

    public static function compareRevisions($entity, $rev_old_id, $rev_new_id)
    {
        if ($rev_old_id > $rev_new_id) {
            $tmp        = $rev_old_id;
            $rev_old_id = $rev_new_id;
            $rev_new_id = $tmp;
        }

        $rev_old = App::findEntity($entity, $rev_old_id);
        $rev_new = App::findEntity($entity, $rev_new_id);

        if (!$rev_old || !$rev_new) {
            return ['rendered_content_diff' => '', 'rendered_title_diff' => ''];
        }

        $old_data = $rev_old->toArray();
        $new_data = $rev_new->toArray();

        $field = self::getContentField($rev_new);

        $rendered_content_diff = null;
        if ($new_data['content']) {
            // We need to go back to find the last change for old
            if (!$old_data['content']) {
                $r = App::getOrm()->createQuery("
                    SELECT r
                    FROM $entity r
                    WHERE r.$field = ?1 AND r.id < ?2 AND r.content != ''
                    ORDER BY r.id DESC
                ")->setMaxResults(1)->setParameters([1 => $rev_new[$field], 2 => $rev_old_id])->getOneOrNullResult();
                if ($r['content']) {
                    $old_data['content'] = $r['content'];
                } else {
                    $old_data['content'] = '';
                }
            }

            $diff = new FineDiff(
                $rev_old['content'],
                $rev_new['content'],
                FineDiff::$wordGranularity
            );
            $rendered_diff = $diff->renderDiffToHTML();

            $rendered_diff         = html_entity_decode($rendered_diff);
            $rendered_content_diff = nl2br($rendered_diff);
        }

        $rendered_title_diff = null;
        if ($new_data['title']) {
            if (!$old_data['title']) {
                $r = App::getOrm()->createQuery("
                    SELECT r
                    FROM $entity r
                    WHERE r.$field = ?1 AND r.id < ?2 AND r.title != ''
                    ORDER BY r.id DESC
                ")->setMaxResults(1)->setParameters([1 => $rev_new[$field], 2 => $rev_old_id])->getOneOrNullResult();
                if ($r['title']) {
                    $old_data['title'] = $r['title'];
                } else {
                    $old_data['title'] = '';
                }
            }

            $diff = new \FineDiff(
                $rev_old['title'],
                $rev_new['title'],
                \FineDiff::$characterGranularity
            );
            $rendered_title_diff = $diff->renderDiffToHTML();
        }

        $use_blob = false;
        if ($field == 'download' && !empty($rev_new['blob'])) {
            $new_data['blob'] = $rev_new['blob'];

            $use_blob = true;
            $r        = $rev_old;
            if (!$r['blob']) {
                $r = App::getOrm()->createQuery("
                    SELECT r
                    FROM $entity r
                    WHERE r.$field = ?1 AND r.id < ?2 AND r.blob IS NOT NULL
                    ORDER BY r.id DESC
                ")->setMaxResults(1)->setParameters([1 => $rev_new[$field], 2 => $rev_old_id])->getOneOrNullResult();
            }

            if ($r['blob']) {
                $old_data['blob'] = $r['blob'];
            } else {
                $old_data['blob'] = null;
            }
        }

        $ret = [
            'rendered_content_diff' => $rendered_content_diff,
            'rendered_title_diff'   => $rendered_title_diff,
        ];

        if ($use_blob) {
            $ret['old_blob'] = $old_data['blob'];
            $ret['new_blob'] = $new_data['blob'];
        }

        return $ret;
    }

    public static function getContentField($content)
    {
        $type = get_class($content);

        switch ($type) {
            case Article::class:
            case ArticleRevision::class:
                return 'article';
                break;

            case News::class:
            case NewsRevision::class:
                return 'news';
                break;

            case Download::class:
            case DownloadRevision::class:
                return 'download';
                break;

            case Feedback::class:
            case FeedbackRevision::class:
                return 'feedback';
                break;

            case Topic::class:
            case TopicRevision::class:
                return 'topic';
                break;
        }

        throw new \InvalidArgumentException("Unknown type `$type`");
    }

    public static function getRevisionClass($content, $entity_name = false)
    {
        $type = get_class($content);

        switch ($type) {
            case Article::class:
                if ($entity_name) {
                    return 'DeskPRO:ArticleRevision';
                } else {
                    return ArticleRevision::class;
                }
                break;

            case News::class:
                if ($entity_name) {
                    return 'DeskPRO:NewsRevision';
                } else {
                    return NewsRevision::class;
                }
                break;

            case Download::class:
                if ($entity_name) {
                    return 'DeskPRO:DownloadRevision';
                } else {
                    return DownloadRevision::class;
                }
                break;

            case Feedback::class:
                if ($entity_name) {
                    return 'DeskPRO:FeedbackRevision';
                } else {
                    return FeedbackRevision::class;
                }
                break;

            case Topic::class:
                if ($entity_name) {
                    return 'DeskPRO:TopicRevision';
                } else {
                    return TopicRevision::class;
                }
                break;
        }

        throw new \InvalidArgumentException("Unknown type `$type`");
    }
}
