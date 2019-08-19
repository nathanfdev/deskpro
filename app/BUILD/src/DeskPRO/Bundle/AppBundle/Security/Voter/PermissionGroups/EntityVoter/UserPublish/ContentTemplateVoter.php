<?php

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\UserPublish;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\ContentTemplate;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\EntityVoter\PermissionGroupEntityVoterInterface;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;

/**
 * Class ContentTemplateVoter.
 */
class ContentTemplateVoter implements PermissionGroupEntityVoterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getEntityClass()
    {
        return ContentTemplate::class;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForAgent($attribute, PermissionGroupContext $context, Person $user)
    {
        /** @var ContentTemplate $contentTemplate */
        $contentTemplate = $context->getParent();
        if ($contentTemplate) {
            $perm = $this->choosePerm($contentTemplate);

            if (!$perm || !$user->hasPerm($perm)) {
                return false;
            }

            if ($contentTemplate->getPerson() && $contentTemplate->getPerson()->getId() == $user->getId()) {
                return true;
            }
        }

        return false;
    }

    protected function choosePerm(ContentTemplate $contentTemplate)
    {
        switch ($contentTemplate->getType()) {
                case ContentTemplate::CONTENT_TYPE_DOWNLOAD:
                    $perm = 'downloads.use';
                    break;
                case ContentTemplate::CONTENT_TYPE_NEWS:
                    $perm = 'news.use';
                    break;
                case ContentTemplate::CONTENT_TYPE_ARTICLE:
                    $perm = 'articles.use';
                    break;
                case ContentTemplate::CONTENT_TYPE_TOPIC:
                    $perm = 'guides.use';
                    break;
                default:
                    $perm = null;
            }

        return $perm;
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttributeForUser($attribute, PermissionGroupContext $context, Person $user)
    {
        // no access for now
        return false;
    }
}
