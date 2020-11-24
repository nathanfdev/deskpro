<?php

namespace DeskPRO\Bundle\PortalBundle\Helper;

use Application\DeskPRO\Entity\CommentAbstract;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Doctrine\ORM\EntityManager;
use Exception;

class ChildCommentHelper
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param LanguageManager $languageManger
     */
    public function __construct(
        EntityManager $em,
        LanguageManager $languageManger

    ) {
        $this->em              = $em;
        $this->languageManager = $languageManger;
    }

    /**
     * @param CommentAbstract $comment
     * @param bool $isHelperCenter
     * @return CommentAbstract|Exception
     */
    public function handleChildComment(CommentAbstract $comment, $isHelperCenter = true)
    {
        if (null === $comment->getParent()) {
            return $comment;
        }

        try {
            $parentComment = $this->em->getRepository(get_class($comment))->find($comment->getParent());

            if (null === $parentComment) {
                $errorMessage = 'helpcenter.flashes.top_level_comment_not_found';
                throw new \RuntimeException(($isHelperCenter) ? $this->languageManager->phrase((array)$errorMessage) : $errorMessage);
            }

            if (null !== $parentComment->getParent()) {
                $errorMessage = 'helpcenter.flashes.reply_top_level_comment_only';
                throw new \RuntimeException(($isHelperCenter) ?
                    $this->languageManager->phrase((array)$errorMessage) : $errorMessage);
            }

            if (false === $parentComment->isReviewed() || !$parentComment->isVisible()) {
                $errorMessage = 'helpcenter.flashes.reply_comment_not_active';
                throw new \RuntimeException(($isHelperCenter) ?
                    $this->languageManager->phrase((array)$errorMessage) : $errorMessage);
            }

            return $comment->setParent($parentComment);
        } catch (Exception $e) {
            $comment->setParent(null);

            return $e;
        }
    }
}
