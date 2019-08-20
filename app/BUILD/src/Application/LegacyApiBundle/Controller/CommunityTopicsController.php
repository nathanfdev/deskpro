<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Application\DeskPRO\Entity\Rating;
use Application\DeskPRO\EntityRepository\Rating as RatingRepository;
use Application\DeskPRO\Searcher\CommunitySearch;
use Orb\Util\Numbers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CommunityTopicsController.
 */
class CommunityTopicsController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction()
    {
        $search_map = [
            'channel_id'          => CommunitySearch::TERM_CHANNEL,
            'channel_id_specific' => CommunitySearch::TERM_CHANNEL_SPECIFIC,
            'label'               => CommunitySearch::TERM_LABEL,
            'status'              => CommunitySearch::TERM_STATUS,
            'status_category_id'  => CommunitySearch::TERM_STATUS_CATEGORY,
        ];

        $terms = [];

        foreach ($search_map as $input => $search_key) {
            $value = $this->in->getCleanValueArray($input, 'raw', 'discard');
            if ($value) {
                $terms[] = ['type' => $search_key, 'op' => 'contains', 'options' => $value];
            }
        }

        $date_created_start = $this->in->getUint('date_created_start');
        $date_created_end   = $this->in->getUint('date_created_end');
        if ($date_created_end) {
            $terms[] = ['type' => CommunitySearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
                'date2' => $date_created_end,
            ]];
        } elseif ($date_created_start) {
            $terms[] = ['type' => CommunitySearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
            ]];
        }

        $order_by = $this->in->getString('order');
        if (!$order_by) {
            $order_by = 'date_created:desc';
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('communityTopic', $terms, $extra, $this->in->getUint('cache_id'), new CommunitySearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $pageIds        = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $communityTopic = App::getEntityRepository('DeskPRO:CommunityTopic')->getByIds($pageIds, true);

        return $this->createApiResponse([
            'page'           => $page,
            'per_page'       => $per_page,
            'total'          => count($ids),
            'cache_id'       => $result_cache->id,
            'communityTopic' => $this->getApiData($communityTopic),
        ]);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newCommunityTopicAction()
    {
        $errors         = [];
        $communityTopic = new CommunityTopic();

        $title = $this->in->getString('title');
        if ($title) {
            $communityTopic->title = $title;
        } else {
            $errors['title'] = ['required_field.title', 'title is required'];
        }

        $content = $this->in->getHtml('content');
        if ($content) {
            $communityTopic->content = $content;
        } else {
            $errors['content'] = ['required_field.content', 'content is required'];
        }

        $status_cat = $this->em->find('DeskPRO:CommunityTopicStatusCategory', $this->in->getUint('status_category_id'));
        if ($status_cat) {
            $communityTopic->setStatusCode($status_cat->status_type.'.'.$status_cat->id);
        } else {
            $status = $this->in->getString('status');
            if (!$status) {
                $status = 'new';
            }
            $communityTopic->setStatusCode($status);
        }

        $cat = $this->em->find('DeskPRO:CommunityChannel', $this->in->getUint('category_id'));
        if ($cat) {
            $communityTopic->category = $cat;
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        $communityTopic->person = $this->person;

        $this->_insertCommunityTopicAttachments($communityTopic);

        $this->em->persist($communityTopic);
        $this->em->flush();

        $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
        if ($labels) {
            $communityTopic->getLabelManager()->setLabelsArray($labels, $this->em);
            $this->em->flush();
        }

        $user_category_id = $this->in->getUint('user_category_id');
        if ($user_category_id) {
            $field         = $this->_getCustomCommunityChannelField();
            $field_manager = $this->container->getSystemService('community_fields_manager');
            $field_manager->saveFormToObject(['field_'.$field->id => $user_category_id], $communityTopic, true);
        }

        return $this->createApiCreateResponse(
            ['id' => $communityTopic->id],
            $this->generateUrl(
                'api_community_topic_view',
                ['topic_id' => $communityTopic->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * Gets information about specific community topic.
     *
     * @param int $communityTopicId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);

        return $this->createApiResponse(['topic' => $communityTopic->toApiData()]);
    }

    /**
     * @param $communityTopicId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postCommunityTopicAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId, 'edit');

        $revs = [];

        $title = $this->in->getString('title');
        if ($title) {
            $communityTopic->title = $title;

            $rev        = ContentRevisionUtil::findOrCreate($communityTopic, 'title', $this->person);
            $rev->title = $communityTopic->title;

            $revs['title'] = $rev;
        }

        $content = $this->in->getString('content');
        if ($content && $content != $communityTopic->content) {
            $communityTopic->content = $this->in->getHtml('content');

            $rev          = ContentRevisionUtil::findOrCreate($communityTopic, ['content'], $this->person);
            $rev->content = $communityTopic->content;

            $revs['content'] = $rev;
        }

        $category_id = $this->in->getUint('category_id');
        if ($category_id) {
            $cat = $this->em->find('DeskPRO:CommunityChannel', $category_id);
            if ($cat) {
                $communityTopic->category = $cat;
            }
        }

        $status_category_id = $this->in->getUint('status_category_id');
        if ($status_category_id) {
            $status_cat = $this->em->find('DeskPRO:CommunityTopicStatusCategory', $this->in->getUint('status_category_id'));
            if ($status_cat) {
                $communityTopic->setStatusCode($status_cat->status_type.'.'.$status_cat->id);
            }
        } else {
            $status = $this->in->getString('status');
            if ($status) {
                $communityTopic->setStatusCode($status);
            }
        }

        $this->_insertCommunityTopicAttachments($communityTopic);

        foreach ($revs as $rev) {
            $this->em->persist($rev);
        }
        $this->em->persist($communityTopic);
        $this->em->flush();

        $user_category_id = $this->in->getUint('user_category_id');
        if ($user_category_id) {
            $field         = $this->_getCustomCommunityChannelField();
            $field_manager = $this->container->getSystemService('community_fields_manager');
            $field_manager->saveFormToObject(['field_'.$field->id => $user_category_id], $communityTopic, true);
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $communityTopicId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteCommunityTopicAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId, 'delete');

        $communityTopic->status_code = 'hidden.deleted';
        $this->em->persist($communityTopic);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $communityTopicId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicVotesAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);
        /** @var RatingRepository $ratingRepository */
        $ratingRepository = App::getEntityRepository(Rating::class);
        $votes            = $ratingRepository->getRatingsFor('community_topic', $communityTopic->getId());

        return $this->createApiResponse(['votes' => $this->getApiData($votes)]);
    }

    /**
     * @param int $communityTopicId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicCommentsAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);
        $comments       = $this->em->getRepository(CommunityTopicComment::class)->getComments($communityTopic);

        return $this->createApiResponse(['comments' => $this->getApiData($comments)]);
    }

    /**
     * @param int $communityTopicId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newCommunityTopicCommentAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);

        $content = $this->in->getString('content');
        if (!$content) {
            return $this->createApiErrorResponse('required_field.content', 'Missing content');
        }

        $person_id = $this->in->getUint('person_id');
        $person    = null;
        if ($person_id) {
            $person = $this->em->getRepository('DeskPRO:Person')->find($person_id);
        }

        $status = $this->in->getString('status');

        $comment                 = new \Application\DeskPRO\Entity\CommunityTopicComment();
        $comment->topic          = $communityTopic;
        $comment->person         = $person ?: $this->person;
        $comment['content']      = $content;
        $comment['status']       = $status ?: 'visible';
        $comment['is_reviewed']  = ($comment['status'] == 'visible' && !$person);
        $comment['date_created'] = new \DateTime();

        $this->em->persist($comment);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $comment->id],
            $this->generateUrl(
                'api_community_topic_comments_get_comment',
                ['communityTopicId' => $communityTopic->id, 'comment_id' => $comment->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $communityTopicId
     * @param int $commentId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicCommentAction($communityTopicId, $commentId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);
        $comment        = $this->em->getRepository(CommunityTopicComment::class)->find($commentId);
        if (!$comment || $comment->topic->id != $communityTopic->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->createApiResponse(['comment' => $comment->toApiData()]);
    }

    /**
     * @param int $communityTopicId
     * @param int $commentId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postCommunityTopicCommentAction($communityTopicId, $commentId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);
        $comment        = $this->em->getRepository(CommunityTopicComment::class)->find($commentId);
        if (!$comment || $comment->topic->id != $communityTopic->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $approved = false;
        $status   = $this->in->getString('status');
        if ($status) {
            $approved        = ($status == 'visible' && $comment->status != 'visible');
            $comment->status = $status;
        }

        $content = $this->in->getString('content');
        if ($content) {
            $comment->content = $content;
        }

        $this->em->persist($comment);
        $this->em->flush();

        if ($approved) {
            $this->_sendCommentApprovedNotification($comment);
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $communityTopicId
     * @param int $commentId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteCommunityTopicCommentAction($communityTopicId, $commentId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);
        $comment        = $this->em->getRepository(CommunityTopicComment::class)->find($commentId);
        if (!$comment || $comment->topic->id != $communityTopic->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $this->em->remove($comment);
        $this->em->flush();

        $this->_sendCommentDeletedNotification($comment);

        return $this->createSuccessResponse();
    }

    /**
     * @param int $communityTopicId
     * @param int $otherCommunityTopicId
     *
     * @throws \Exception
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mergeCommunityTopicsAction($communityTopicId, $otherCommunityTopicId)
    {
        $communityTopic      = $this->_getCommunityTopicOr404($communityTopicId, 'edit');
        $otherCommunityTopic = $this->_getCommunityTopicOr404($otherCommunityTopicId, 'edit');

        if (!$this->person->PermissionsManager->PublishChecker->canEdit($communityTopic)
            || !$this->person->PermissionsManager->PublishChecker->canEdit($otherCommunityTopic)
            || !$this->person->PermissionsManager->PublishChecker->canDelete($otherCommunityTopic)
        ) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        try {
            $this->em->beginTransaction();
            $merge = new \Application\DeskPRO\Community\CommunityTopicsMerge($this->person, $communityTopic, $otherCommunityTopic);
            $merge->merge();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $communityTopicId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicAttachmentsAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);

        return $this->createApiResponse(['attachments' => $this->getApiData($communityTopic->attachments)]);
    }

    /**
     * @param int $communityTopicId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newComunityTopicAttachmentAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId, 'edit');

        $file = $this->request->files->get('attach');
        if (is_array($file)) {
            $file = reset($file);
        }

        if ($file) {
            $accept = $this->container->getAttachmentAccepter();

            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $blob = $accept->accept($file);
            } else {
                $message = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createApiErrorResponse($error['error_code'], $message);
            }
        } else {
            $blob_id = $this->in->getUint('attach_id');
            $blob    = $this->em->find('DeskPRO:Blob', $blob_id);
            if (!$blob) {
                return $this->createApiErrorResponse('invalid_argument.attach_id', 'attach_id not found');
            }
        }

        $attach = $this->_addCommunityTopicAttachment($blob, $communityTopic);

        $this->em->persist($communityTopic);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $attach->id],
            $this->generateUrl(
                'api_community_topic_attachment_get',
                ['topic_id' => $communityTopic->id, 'attachment_id' => $attach->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $communityTopicId
     * @param int $attachmentId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicAttachmentAction($communityTopicId, $attachmentId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);
        $exists         = false;
        foreach ($communityTopic->attachments as $attachment) {
            if ($attachment->id == $attachmentId) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(['exists' => $exists]);
    }

    /**
     * @param int $communityTopicId
     * @param int $attachmentId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteCommunityTopicAttachmentAction($communityTopicId, $attachmentId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);
        foreach ($communityTopic->attachments as $k => $attachment) {
            if ($attachment->id == $attachmentId) {
                $communityTopic->attachments->remove($k);
                $this->em->remove($attachment);
                break;
            }
        }

        $this->em->persist($communityTopic);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $communityTopicId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicLabelsAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);

        return $this->createApiResponse(['labels' => $this->getApiData($communityTopic->labels)]);
    }

    /**
     * @param int $communityTopicId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postCommunityTopicLabelsAction($communityTopicId)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId, 'edit');
        $label          = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $communityTopic->getLabelManager()->addLabel($label);
        $this->em->persist($communityTopic);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['label' => $label],
            $this->generateUrl(
                'api_community_topic_label',
                ['communityTopicId' => $communityTopic->id, 'label' => $label],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int    $communityTopicId
     * @param string $label
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCommunityTopicLabelAction($communityTopicId, $label)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId);

        if ($communityTopic->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(['exists' => true]);
        } else {
            return $this->createApiResponse(['exists' => false]);
        }
    }

    /**
     * @param $communityTopicId
     * @param $label
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteCommunityTopicLabelAction($communityTopicId, $label)
    {
        $communityTopic = $this->_getCommunityTopicOr404($communityTopicId, 'edit');

        $communityTopic->getLabelManager()->removeLabel($label);
        $this->em->persist($communityTopic);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getValidatingCommentsAction()
    {
        $comments   = $this->em->getRepository(CommunityTopicComment::class)->getValidatingComments();
        $entity_key = 'community_topic';
        $output     = [];
        foreach ($comments as $key => $value) {
            $output[$key] = $value->toApiData(false, true);
            if ($value->$entity_key) {
                $output[$key][$entity_key] = $value->$entity_key->toApiData(false, false);
            }
        }

        return $this->createApiResponse(['comments' => $output]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getChannelsAction()
    {
        $channels = $this->em->getRepository('DeskPRO:CommunityChannel')->getFlatHierarchy();

        return $this->createApiResponse(['categories' => $channels]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getStatusCategoriesAction()
    {
        $categories = $this->em->getRepository('DeskPRO:CommunityTopicStatusCategory')->findAll();

        return $this->createApiResponse(['categories' => $this->getApiData($categories)]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomChannelsAction()
    {
        $field    = $this->_getCustomCommunityChannelField();
        $children = $field->getAllChildren();

        return $this->createApiResponse(['categories' => $this->getApiData($children)]);
    }

    protected function _insertCommunityTopicAttachments(CommunityTopic $communityTopic)
    {
        $attachments = $this->request->files->get('attach');
        if (!is_array($attachments)) {
            $attachments = [$attachments];
        }
        $accept = $this->container->getAttachmentAccepter();

        foreach ($attachments as $file) {
            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $blob = $accept->accept($file);
                $this->_addCommunityTopicAttachment($blob, $communityTopic);
            }
        }

        foreach ($this->in->getCleanValueArray('attach_id') as $blob_id) {
            $this->_addCommunityTopicAttachment($blob_id, $communityTopic);
        }
    }

    protected function _addCommunityTopicAttachment($blob_id, CommunityTopic $communityTopic)
    {
        if ($blob_id instanceof \Application\DeskPRO\Entity\Blob) {
            $blob = $blob_id;
        } else {
            $blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);
        }

        if ($blob) {
            $attach           = new \Application\DeskPRO\Entity\CommunityTopicAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            $communityTopic->addAttachment($attach);

            return $attach;
        } else {
            return false;
        }
    }

    /**
     * @param Brand $brand
     *
     * @return \Application\DeskPRO\Entity\CustomDefCommunityTopic
     */
    protected function _getCustomCommunityChannelField(Brand $brand = null)
    {
        if (!$brand) {
            $brand = $this->get('default_brand_finder')->getDefaultBrand();
        }

        return $this->container->getSystemService('custom_community_channels')->getParentCategory($brand);
    }

    /**
     * @param int $id
     *
     *@throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return CommunityTopic
     */
    protected function _getCommunityTopicOr404($id, $check_perm = false)
    {
        $communityTopic = $this->em->getRepository(CommunityTopic::class)->findOneById($id);

        if (!$communityTopic) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no topic with ID $id");
        }

        if ($check_perm) {
            if ($check_perm == 'edit' && !$this->person->PermissionsManager->PublishChecker->canEdit($communityTopic)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }

            if ($check_perm == 'delete' && !$this->person->PermissionsManager->PublishChecker->canDelete($communityTopic)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }
        }

        return $communityTopic;
    }
}
