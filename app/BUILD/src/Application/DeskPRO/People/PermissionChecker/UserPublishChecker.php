<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionChecker;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\HttpFoundation\Session as HttpSession;

class UserPublishChecker extends AbstractChecker
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @param \Application\DeskPRO\Entity\Article $article
     *
     * @return bool
     */
    public function canViewArticle(Article $article)
    {
        if (!$this->person->hasPerm('articles.use')) {
            return false;
        }

        // Only agents can view non-published
        if ($article->getStatus() != 'published' && $article->getStatus() != 'archived' && !$this->person->is_agent) {
            return false;
        }

        $perms = $this->person->PermissionsManager->ArticleCategories->getAllowedCategories();
        $perms = array_flip($perms);

        if (!count($article->categories)) {
            return true;
        }

        foreach ($article->categories as $cat) {
            if (isset($perms[$cat->id])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Application\DeskPRO\Entity\News $news
     *
     * @return bool
     */
    public function canViewNews(News $news)
    {
        if (!$this->person->hasPerm('news.use')) {
            return false;
        }

        // Only agents can view non-published
        if ($news->getStatus() != 'published' && $news->getStatus() != 'archived' && !$this->person->is_agent) {
            return false;
        }

        if (!$news->category || $this->person->PermissionsManager->NewsCategories->isCategoryAllowed($news->category->getId())) {
            return true;
        }

        return false;
    }

    /**
     * @param \Application\DeskPRO\Entity\Download $download
     *
     * @return bool
     */
    public function canViewDownload($download)
    {
        if (!$this->person->hasPerm('downloads.use')) {
            return false;
        }

        // Only agents can view non-published
        if ($download->getStatus() != 'published' && $download->getStatus() != 'archived' && !$this->person->is_agent) {
            return false;
        }

        if (!$download->category || $this->person->PermissionsManager->DownloadCategories->isCategoryAllowed($download->category->getId())) {
            return true;
        }

        return false;
    }

    /**
     * @param \Application\DeskPRO\Entity\Topic $topic
     *
     * @return bool
     */
    public function canViewTopic($topic)
    {
        if (!$this->person->hasPerm('guides.use')) {
            return false;
        }

        // Only agents can view non-published
        if ($topic->getStatus() != 'published' && $topic->getStatus() != 'archived' && !$this->person->is_agent) {
            return false;
        }

        if (!$topic->getGuide() || $this->person->PermissionsManager->Guides->isCategoryAllowed($topic->getGuide()->getId())) {
            return true;
        }

        return false;
    }

    /**
     * @param \Application\DeskPRO\Entity\Feedback $feedback
     *
     * @return bool
     */
    public function canViewFeedback(Feedback $feedback, HttpSession $user_session = null)
    {
        if (!$this->person->hasPerm('feedback.use')) {
            return false;
        }

        // Only agents can view non-published
        if ($feedback->getStatus() == 'hidden' && !$this->person->is_agent) {
            // But still show the user their own submitted feedback
            if ($feedback->person && $feedback->person->getId() == $this->person->getId()) {
                return true;
            }
            if ($user_session) {
                $submitted_feedback = $user_session->get('submitted_feedback');
                if (is_array($submitted_feedback) && in_array($feedback->getId(), $submitted_feedback)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
