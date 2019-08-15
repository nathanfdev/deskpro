<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\CommunityTopicResults;
use Application\AgentBundle\Form\Model\NewCommunityTopic;
use Application\AgentBundle\Form\Type\NewCommunityTopic as NewCommunityTopicTypeOld;
use Application\AgentBundle\Validator\NewCommunityTopicValidator;
use Application\DeskPRO\Community\CommunityTopicModerate;
use Application\DeskPRO\Community\CommunityTopicsCollection;
use Application\DeskPRO\Community\CommunityTopicsMerge;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CommunityChannel;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Entity\SearchStickyResult;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\CommunityChannel as CommunityChannelRepository;
use Application\DeskPRO\EntityRepository\CommunityTopic as CommunityTopicRepository;
use Application\DeskPRO\EntityRepository\CommunityTopicComment as CommunityTopicCommentRepository;
use Application\DeskPRO\EntityRepository\CommunityTopicStatusCategory as CommunityTopicStatusCategoryRepository;
use Application\DeskPRO\EntityRepository\PersonPref as PersonPrefRepository;
use Application\DeskPRO\EntityRepository\SearchLog as SearchLogRepository;
use Application\DeskPRO\EntityRepository\SearchStickyResult as SearchStickyResultRepository;
use Application\DeskPRO\Labels\LabelLister;
use Application\DeskPRO\People\PermissionChecker\PublishChecker;
use Application\DeskPRO\Publish\Community\GroupingCounter;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use DeskPRO\Bundle\AppBundle\Entity\TicketCommunityTopicLink;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator\CommunityTopicLinkGenerator;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Handles ticket searches.
 */
class CommunityTopicsController extends AbstractController
{
    //###########################################################################
    // get-section-data
    //###########################################################################

    /**
     * @return Response
     */
    public function getSectionDataAction()
    {
        if (!$this->person->hasPerm('agent_publish.use')) {
            throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
        }

        $selectedBrandId = $this->in->getUInt('brand_id');
        if (!$selectedBrandId) {
            $selectedBrandId = (int) $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }
        if (!$selectedBrandId) {
            $selectedBrandId = $this->get('brand_stack')->getDefaultBrand()->getId();
        }

        /** @var CommunityTopicRepository $communityTopicRepository */
        /* @var CommunityChannelRepository $CommunityChannelRepository */
        /* @var CommunityTopicStatusCategoryRepository $communityTopicStatusCategoryRepository */
        /* @var CommunityTopicCommentRepository $CommunityTopicCommentRepository */
        $communityTopicRepository               = $this->em->getRepository(CommunityTopic::class);
        $CommunityChannelRepository             = $this->em->getRepository(CommunityChannel::class);
        $communityTopicStatusCategoryRepository = $this->em->getRepository(CommunityTopicStatusCategory::class);
        $CommunityTopicCommentRepository        = $this->em->getRepository(CommunityTopicComment::class);

        $counts = [
            'community_topics_awaiting_validation' => $communityTopicRepository->countAwaitingValidation(),
            'comments_awaiting_validation'         => $CommunityTopicCommentRepository->countAwaitingValidation(),
        ];

        $statusCounts = [
            'active' => $communityTopicRepository->countActiveGrouped($selectedBrandId),
            'closed' => $communityTopicRepository->countClosedGrouped($selectedBrandId),
            'hidden' => $communityTopicRepository->countHiddenGrouped($selectedBrandId),
        ];

        $channelCounts = $communityTopicRepository->countAllChannelsGrouped();

        $activeStatusCategories = $communityTopicStatusCategoryRepository->getActiveCategories($selectedBrandId);
        $closedStatusCategories = $communityTopicStatusCategoryRepository->getClosedCategories($selectedBrandId);
        $communityChannels      = array_filter($CommunityChannelRepository->getFlatHierarchy(), function ($channel) use ($selectedBrandId) {
            return $channel['brand_id'] === $selectedBrandId;
        });

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();

        $labelLister            = new LabelLister('community_topics');
        $communityTopicTagIndex = $labelLister->getIndexList();

        $data = [
            'section_html' => $this->renderView(
                'AgentBundle:Community:window-section.html.twig',
                [
                    'counts'                    => $counts,
                    'status_counts'             => $statusCounts,
                    'channel_counts'            => $channelCounts,
                    'community_channels'        => $communityChannels,
                    'active_status_cats'        => $activeStatusCategories,
                    'closed_status_cats'        => $closedStatusCategories,
                    'community_topic_tag_index' => $communityTopicTagIndex,
                    'brands'                    => $brands,
                    'selected_brand_id'         => $selectedBrandId,
                ]
            ),
        ];

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // validating content actions
    //###########################################################################

    /**
     * @return Response
     */
    public function listValidatingContentAction()
    {
        $perPage = 25;

        /** @var \Application\DeskPRO\EntityRepository\CommunityTopic $communityTopicRepository */
        $communityTopicRepository = $this->em->getRepository(CommunityTopic::class);
        $pageinfo                 = $total                 = null;

        $currentPage = $this->in->getUInt('page') ?: 1;
        $offset      = (($currentPage - 1 >= 0) ? $currentPage - 1 : 1) * $perPage;

        if (!@$_REQUEST['_partial']) {
            $total    = $communityTopicRepository->countAwaitingValidation();
            $pageinfo = Numbers::getPaginationPages($total, $currentPage, $perPage);
        }

        $contentValidating = $communityTopicRepository->getAwaitingValidation($perPage, $offset);
        $info              = [];
        foreach ($contentValidating as $communityTopic) {
            /* @var CommunityTopic $communityTopic */
            $lastRevision = $communityTopic->getRevisions()->current();
            $info[]       = [
                'info' => [
                    'content_type' => 'community_topic',
                    'content_id'   => $communityTopic->getId(),
                    'revision_id'  => $lastRevision ? $lastRevision->getId() : null,
                    'date_created' => $communityTopic->getDateCreated()->format('Y-m-d H:i:s'),
                ],
                'obj' => $communityTopic,
            ];
        }

        $tpl = @$_REQUEST['_partial']
            ? 'AgentBundle:Publish:validating-content-page.html.twig'
            : 'AgentBundle:Publish:validating-content.html.twig';

        return $this->render(
            $tpl,
            [
                'single_type'        => 'community_topic',
                'content_validating' => $info,
                'total'              => $total,
                'pageinfo'           => $pageinfo,
            ]
        );
    }

    //###########################################################################
    // view
    //###########################################################################

    /**
     * @param $communityTopicId
     *
     * @return Response
     */
    public function viewAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        /** @var PublishChecker $publishChecker */
        /* @var SearchLogRepository $searchLogRepository */
        /* @var PersonPrefRepository $personPrefRepository */
        /* @var CommunityChannelRepository $CommunityChannelRepository */
        /* @var SearchStickyResultRepository $searchStickyResultRepository */
        /* @var CommunityTopicStatusCategoryRepository $communityTopicStatusCategoryRepository */
        $publishChecker                         = $this->person->getPermissionsManager()->get('PublishChecker');
        $fieldManager                           = $this->container->getSystemService('community_fields_manager');
        $searchLogRepository                    = $this->em->getRepository(SearchLog::class);
        $personPrefRepository                   = $this->em->getRepository(PersonPref::class);
        $CommunityChannelRepository             = $this->em->getRepository(CommunityChannel::class);
        $searchStickyResultRepository           = $this->em->getRepository(SearchStickyResult::class);
        $communityTopicStatusCategoryRepository = $this->em->getRepository(CommunityTopicStatusCategory::class);

        $customFields              = $fieldManager->getDisplayArrayForObject($communityTopic);
        $communityTopicComments    = [];
        $communityTopicCommentsRaw = $communityTopic->getComments();

        foreach ($communityTopicCommentsRaw as $c) {
            if ($c->status != 'temp') {
                $communityTopicComments[] = $c;
            }
        }

        $state       = $personPrefRepository->getPrefForPersonId('agent.ui.state.editcommunity', $this->person->id);
        $channel     = $communityTopic->getChannel();
        $channelPath = $channel->getTreeParents();

        $ratedSearches           = $searchLogRepository->getRatedSearchesFor('community', $communityTopic['id'], 'counted');
        $relatedFinder           = new RelatedContentFinder($this->person, $communityTopic);
        $relatedContent          = $relatedFinder->getRelatedEntities(true);
        $communityTopicRevisions = $communityTopic->getRevisions();
        $stickySearchWords       = $searchStickyResultRepository->getWordsForObject($communityTopic);
        $activeStatusCategories  = $communityTopicStatusCategoryRepository->getActiveCategories($communityTopic->getBrand());
        $closedStatusCategories  = $communityTopicStatusCategoryRepository->getClosedCategories($communityTopic->getBrand());
        $communityChannels       = array_filter($CommunityChannelRepository->getInHierarchy(), function ($channel) use ($communityTopic) {
            return $communityTopic->getBrand() && $channel['brand_id'] === $communityTopic->getBrand()->getId();
        });

        //@TODO: related entities fetching optimization
        $communityTopicLinksRepo    = $this->em->getRepository(TicketCommunityTopicLink::class);
        $ticketCommunityTopicsLinks = $communityTopicLinksRepo->findByTopic($communityTopic);

        //@TODO: select only needed data to display persons
        $subscribedIds = $this->em->getRepository('DeskPRO:CommunityTopicSubscription')->getSubscribedPersonIds($communityTopic);
        // limit to 250
        $subscribedIds     = array_slice($subscribedIds, 0, 250);
        $subscribedPersons = $this->em->getRepository('DeskPRO:Person')->findById($subscribedIds);

        $perms = [
            'can_edit'   => $publishChecker->canEdit($communityTopic),
            'can_delete' => $publishChecker->canDelete($communityTopic),
        ];

        return $this->render(
            'AgentBundle:Community:view.html.twig',
            [
                'topic'                         => $communityTopic,
                'topic_comments'                => $communityTopicComments,
                'topic_revisions'               => $communityTopicRevisions,
                'state'                         => $state,
                'channel'                       => $channel,
                'channel_path'                  => $channelPath,
                'custom_fields'                 => $customFields,
                'rated_searches'                => $ratedSearches,
                'related_content'               => $relatedContent,
                'sticky_search_words'           => $stickySearchWords,
                'community_channels'            => $communityChannels,
                'active_status_cats'            => $activeStatusCategories,
                'closed_status_cats'            => $closedStatusCategories,
                'ticket_community_topics_links' => $ticketCommunityTopicsLinks,
                'subscribed_persons'            => $subscribedPersons,
                'perms'                         => $perms,
                'permalink'                     => $this->get('object_router')->getPortalUrl($communityTopic, CommunityTopicLinkGenerator::TYPE_PERMALINK),
            ]
        );
    }

    public function viewRevisionsAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        return $this->render('AgentBundle:Community:view-revisions-tab.html.twig', [
            'topic' => $communityTopic,
        ]);
    }

    /**
     * @param $communityTopicId
     *
     * @return Response
     */
    public function whoVotedAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        $communityTopic_votes = $communityTopic->votes->toArray();

        return $this->render(
            'AgentBundle:Community:view-who-voted.html.twig',
            [
                'community_topic'       => $communityTopic,
                'community_topic_votes' => $communityTopic_votes,
            ]
        );
    }

    public function ajaxGetChannelsByBrandAction($brand_id)
    {
        $communityChannels = array_filter($this->em->getRepository(CommunityChannel::class)->getFlatHierarchy(), function ($channel) use ($brand_id) {
            return $channel['brand_id'] === (int) $brand_id;
        });

        return $this->render('AgentBundle:Common:select-standard.html.twig', [
            'name'             => 'newcomunitytopic[channel_id]',
            'id'               => '_cat',
            'add_classname'    => 'channel_id',
            'add_attr'         => '',
            'with_blank'       => 0,
            'blank_title'      => '',
            'categories'       => $communityChannels,
            'allow_parent_sel' => true,
        ]);
    }

    public function ajaxGetStatusesByBrandAction($brand_id)
    {
        $communityTopicStatusCategoryRepository = $this->em->getRepository(CommunityTopicStatusCategory::class);

        $activeStatusCategories = $communityTopicStatusCategoryRepository->getActiveCategories($brand_id);
        $closedStatusCategories = $communityTopicStatusCategoryRepository->getClosedCategories($brand_id);

        return $this->render('AgentBundle:Common:select-community-topic-status.html.twig', [
            'name'               => 'newcomunitytopic[status_code]',
            'id'                 => '_cat',
            'add_classname'      => 'status_id',
            'add_attr'           => '',
            'with_blank'         => 0,
            'blank_title'        => '',
            'active_status_cats' => $activeStatusCategories,
            'closed_status_cats' => $closedStatusCategories,
            'allow_parent_sel'   => true,
        ]);
    }

    /**
     * @param $communityTopicId
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function ajaxSaveEditablesAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if (!$publishChecker->canEdit($communityTopic)) {
            return $this->createJsonResponse(['success' => false]);
        }

        $ret = '';

        switch ($this->in->getString('action')) {
            case 'title':
                $value                   = $this->in->getString('title');
                $communityTopic['title'] = $value;
                $ret                     = ['html' => htmlspecialchars($communityTopic['title'])];
                break;
        }

        $this->em->transactional(
            function ($em) use ($communityTopic) {
                /* @var EntityManager $em */
                $em->persist($communityTopic);
                $em->flush();
            }
        );

        return $this->createJsonResponse(
            [
                'success'            => true,
                'community_topic_id' => $communityTopic->getId(),
                'html'               => $ret,
            ]
        );
    }

    /**
     * @param $communityTopicId
     * @param $channelId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function ajaxUpdateChannelAction($communityTopicId, $channelId)
    {
        $communityTopic = $this->getTopic($communityTopicId);
        $channel        = $this->em->find(CommunityChannel::class, $channelId);
        $communityTopic->setChannel($channel);

        $this->em->transactional(
            function ($em) use ($communityTopic) {
                /* @var EntityManager $em */
                $em->persist($communityTopic);
                $em->flush();
            }
        );

        return $this->createJsonResponse(
            [
                'success'            => true,
                'community_topic_id' => $communityTopic['id'],
            ]
        );
    }

    /**
     * @param $communityTopicId
     * @param $status_code
     *
     * @throws \Exception
     *
     * @return null|Response
     */
    public function ajaxUpdateStatusAction($communityTopicId, $status_code)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        if ($response = $this->checkPermissions($communityTopic)) {
            return $response;
        }

        $communityTopic->setStatusCode($status_code);
        $this->em->transactional(
            function ($em) use ($communityTopic) {
                /* @var EntityManager $em */
                $em->persist($communityTopic);
                $em->flush();
            }
        );

        return $this->createJsonResponse(
            [
                'success'            => true,
                'community_topic_id' => $communityTopic->getId(),
            ]
        );
    }

    /**
     * @param $communityTopicId
     *
     * @throws \Exception
     *
     * @return null|Response
     */
    public function ajaxSaveCustomFieldsAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        if ($response = $this->checkPermissions($communityTopic)) {
            return $response;
        }

        $this->em->beginTransaction();

        try {
            $fieldManager     = $this->container->getSystemService('community_fields_manager');
            $postCustomFields = $this->request->request->get('custom_fields', []);
            if (!empty($postCustomFields)) {
                $fieldManager->saveFormToObject($postCustomFields, $communityTopic);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $customFields = $fieldManager->getDisplayArrayForObject($communityTopic);

        return $this->render(
            'AgentBundle:Community:view-customfields-rendered-rows.html.twig',
            [
                'community_topic' => $communityTopic,
                'custom_fields'   => $customFields,
            ]
        );
    }

    /**
     * @param $communityTopicId
     *
     * @return null|Response
     */
    public function ajaxSaveLabelsAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        if ($response = $this->checkPermissions($communityTopic)) {
            return $response;
        }

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $communityTopic->getLabelManager()->setLabelsArray($labels);
        $this->em->persist($communityTopic);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    /**
     * @param $communityTopicId
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return Response
     */
    public function ajaxSaveCommentAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        if (!$this->in->getString('content')) {
            throw $this->createNotFoundException();
        }

        $comment = new CommunityTopicComment();
        $comment
            ->setIsReviewed(true)
            ->setContent($this->in->getString('content'))
            ->setStatus($this->in->getBool('agent_only') ? 'agent' : 'visible');

        $communityTopic->addComment($comment);
        $comment->getPerson() || $comment->setPerson($this->person);

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($comment);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        return $this->render(
            'AgentBundle:Community:view-comment.html.twig',
            [
                'comment' => $comment,
            ]
        );
    }

    /**
     * @param $communityTopicId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function ajaxSaveAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        $rev    = null;
        $action = $this->in->getString('action');

        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if ($action == 'delete' && !$publishChecker->canDelete($communityTopic)) {
            return $this->createJsonResponse(['success' => false]);
        } elseif ($response = $this->checkPermissions($communityTopic)) {
            return $response;
        }

        $data = ['success' => 1];

        $this->em->beginTransaction();

        switch ($action) {
            case 'status':
            case 'delete':
                if ($action == 'delete') {
                    $communityTopic['status_code'] = 'hidden.deleted';
                } else {
                    $communityTopic['status_code'] = $this->in->getString('status');
                }
                break;

            case 'undelete':
                $communityTopic['status_code'] = 'new';
                $communityTopic->setSlug(null);
                break;

            case 'title':
                $communityTopic['title'] = $this->in->getString('title');
                $rev                     = ContentRevisionUtil::findOrCreate($communityTopic, 'title', $this->person);
                $rev['title']            = $communityTopic['title'];
                break;

            case 'slug':
                $communityTopic['slug'] = Strings::slugifyTitle($this->in->getString('slug')) ?: 'view';
                $data['slug']           = $communityTopic['slug'];
                break;

            case 'add-related':
                $updater = new RelatedContentUpdate($communityTopic);
                $updater->addRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-related':
                $updater = new RelatedContentUpdate($communityTopic);
                $updater->removeRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-blob':
                foreach ($communityTopic->getAttachments() as $k => $attach) {
                    if ($attach->blob['id'] == $this->in->getUInt('blob_id')) {
                        $communityTopic->getAttachments()->remove($k);
                        $this->em->remove($attach);
                        break;
                    }
                }

                break;

            case 'content':
                /** @var PersonPrefRepository $personPrefRepository */
                $personPrefRepository = $this->em->getRepository('DeskPRO:PersonPref');
                $personPrefRepository->deletePrefForPersonId('agent.ui.state.editcommunity', $this->person->id);

                $content = $this->person->hasPerm('agent_publish.can_insert_html')
                    ? $this->in->getCleanValue('content', 'string', null, ['noclean' => true])
                    : $this->in->getCleanValue('content', 'html');

                $communityTopic->setContent($content);

                $inlineBlobIds = $this->in->getCleanValueArray('blob_inline_ids', 'int');
                $this->get('attachment_helper')->processInlineBlobs($content, $inlineBlobIds);

                $data['content_html'] = $this->renderView(
                    'AgentBundle:Community:view-content-tab.html.twig',
                    ['topic' => $communityTopic]
                );

                $rev            = ContentRevisionUtil::findOrCreate($communityTopic, ['content'], $this->person);
                $rev['content'] = $communityTopic['content'];

                break;

            case 'channel':
                $channel = $this->em->find('DeskPRO:CommunityChannel', $this->in->getUInt('channel_id'));
                if ($cat) {
                    $communityTopic['channel'] = $channel;
                    $data['channel_id']        = $channel['id'];
                }
                break;
        }

        $this->em->persist($communityTopic);
        foreach ($communityTopic->getAttachments() as $attachment) {
            $this->em->persist($attachment->getBlob()->setIsTemp(true));
        }
        if ($rev) {
            $this->em->persist($rev);
        }

        $this->em->flush();
        $this->em->commit();

        if ($rev) {
            $data['revision_id'] = $rev['id'];
        } else {
            $data['revision_id'] = null;
        }

        if (in_array($action, ['undelete', 'delete'], true)) {
            $data['slug'] = $communityTopic->getSlug();
        }

        return $this->createJsonResponse($data);
    }

    /**
     * @param $communityTopicId
     *
     * @return Response
     */
    public function ajaxSubscribePersonAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        if (!$person = $this->em->find(Person::class, $this->in->getUInt('person_id'))) {
            throw $this->createNotFoundException(sprintf(
                'There is no person with ID %s',
                $this->in->getUInt('person_id')
            ));
        }

        $this->get('community_subscription_helper')->subscribePersons($communityTopic, [$person]);

        return $this->createJsonResponse(['success' => 1]);
    }

    /**
     * @param $communityTopicId
     *
     * @return Response
     */
    public function ajaxUnsubscribePersonAction($communityTopicId)
    {
        $communityTopic = $this->getTopic($communityTopicId);

        if (!$person = $this->em->find(Person::class, $this->in->getUInt('person_id'))) {
            throw $this->createNotFoundException(sprintf(
                'There is no person with ID %s',
                $this->in->getUInt('person_id')
            ));
        }

        $this->get('community_subscription_helper')->unsubscribePerson($communityTopic, $person);

        return $this->createJsonResponse(['success' => 1]);
    }

    //###########################################################################
    // merge
    //###########################################################################

    /**
     * @param     $communityTopicId
     * @param int $otherCommunityTopicId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function mergeOverlayAction($communityTopicId, $otherCommunityTopicId = 0)
    {
        $communityTopic = $this->em->find(CommunityTopic::class, $communityTopicId);

        if ($otherCommunityTopicId && $otherCommunityTopicId != $communityTopicId) {
            $otherCommunityTopic = $this->em->find(CommunityTopic::class, $otherCommunityTopicId);
        } else {
            $otherCommunityTopic = false;
        }

        return $this->render(
            'AgentBundle:Community:merge-overlay.html.twig',
            [
                'topic'       => $communityTopic,
                'other_topic' => $otherCommunityTopic,
            ]
        );
    }

    /**
     * Merge a ticket interface.
     *
     * @param $communityTopicId
     * @param $otherCommunityTopicId
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mergeAction($communityTopicId, $otherCommunityTopicId)
    {
        $communityTopic      = $this->getTopic($communityTopicId);
        $otherCommunityTopic = $this->getTopic($otherCommunityTopicId);

        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if (!$publishChecker->canEdit($communityTopic)
            || !$publishChecker->canEdit($otherCommunityTopic)
            || !$publishChecker->canDelete($otherCommunityTopic)
        ) {
            return $this->createJsonResponse(['success' => false]);
        }

        $oldCommunityTopicId = $otherCommunityTopic['id'];

        try {
            $this->em->beginTransaction();
            $merge = new CommunityTopicsMerge($this->person, $communityTopic, $otherCommunityTopic);
            $merge->merge();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $this->createJsonResponse(
            [
                'success' => true,
                'id'      => $communityTopic->getId(),
                'old_id'  => $oldCommunityTopicId,
            ]
        );
    }

    //###########################################################################
    // filters
    //###########################################################################

    /**
     * Any general search. For example, status, channel or label.
     *
     * @return Response
     */
    public function filterListAction()
    {
        $resultHelper = CommunityTopicResults::newFromRequest($this);

        return $this->renderList(
            $resultHelper,
            null,
            ['list_type' => 'filter']
        );
    }

    /**
     * A shortcut to run a filter on a channel.
     *
     * @param int $channelId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function channelsListAction($channelId)
    {
        $topResultHelper = CommunityTopicResults::newFromRequest(
            $this,
            [
                'specific_terms' => [
                    'channel' => ['type' => 'channel', 'op' => 'is', 'channel' => $channelId],
                    'status'  => ['type' => 'status', 'op' => 'not', 'status' => 'hidden'],
                ],
            ]
        );

        if ($this->in->getString('subgroup')) {
            $resultHelper = CommunityTopicResults::newFromRequest(
                $this,
                [
                    'specific_terms' => [
                        'channel' => ['type' => 'channel', 'op' => 'is', 'channel' => $channelId],
                        'status'  => ['type' => 'status', 'op' => 'is', 'status' => $this->in->getString('subgroup')],
                    ],
                ]
            );
        } else {
            $resultHelper = $topResultHelper;
        }

        $channel = $this->em->find('DeskPRO:CommunityChannel', $channelId);

        $grouping = new GroupingCounter();
        $grouping->setGrouping('status')->setIds($topResultHelper->getTopicIds());
        $grouped      = $grouping->getDisplayArray();
        $grouped_info = [];

        if (!$channel->parent) {
            $grouped_key = $channel->getId();

            $t = 0;
            if (isset($grouped['items'][$grouped_key])) {
                $grouped_info = Arrays::mergeAssoc($grouped_info, [$grouped_key => $grouped['items'][$grouped_key]]);
                $t            = $grouped['items'][$grouped_key]['total'];
            }

            $grouped_info[-1] = ['id' => -1, 'title' => 'TOTAL', 'total' => $t];
        } else {
            $grouped_key = $channel->getId();
            $t           = 0;
            foreach ($channel->children as $c) {
                $k = $c['id'];
                if (isset($grouped['items'][$k])) {
                    $grouped_info = Arrays::mergeAssoc($grouped_info, [$k => $grouped['items'][$k]]);
                    $t += $grouped['items'][$k]['total'];
                }
            }

            $grouped_info[-1] = ['id' => -1, 'title' => 'TOTAL', 'total' => $t];
        }

        return $this->renderList(
            $resultHelper,
            null,
            [
                'list_type'    => 'channel',
                'brand_id'     => $channel->getBrand() ? $channel->getBrand()->getId() : null,
                'channel_id'   => $channelId,
                'page_title'   => $channel->getFullTitle(),
                'grouped'      => $grouped,
                'grouped_info' => $grouped_info,
                'grouped_key'  => $grouped_key,
                'subgroup'     => $this->in->getString('subgroup'),
            ]
        );
    }

    /**
     * A shortcut to run a filter on a label.
     *
     * @param int    $brand_id
     * @param string $label
     *
     * @return Response
     */
    public function labelListAction($brand_id, $label)
    {
        $resultHelper = CommunityTopicResults::newFromRequest(
            $this,
            [
                'specific_terms' => [
                    ['type' => 'brand', 'op' => 'is', 'brand' => $brand_id],
                    ['type' => 'label', 'op' => 'is', 'label' => $label],
                    ['type' => 'status', 'op' => 'not', 'status' => 'hidden'],
                ],
            ]
        );

        return $this->renderList(
            $resultHelper,
            null,
            [
                'list_type'         => 'label',
                'label'             => $label,
                'page_title'        => $label,
                'selected_brand_id' => $brand_id,
            ]
        );
    }

    /**
     * A shortcut to run a filter on a status.
     *
     * @param int    $brand_id
     * @param string $status
     *
     * @return Response
     */
    public function statusListAction($brand_id, $status)
    {
        // $status can be either a top-level name like active, closed or hidden,
        // or an integer which will be treated as a status category (Active > Planned for example)

        if (strpos($status, '.') !== false) {
            list($status, $v_status) = explode('.', $status);
            $topResultHelper         = CommunityTopicResults::newFromRequest(
                $this,
                [
                    'specific_terms' => [
                        'brand'    => ['type' => 'brand', 'op' => 'is', 'brand' => $brand_id],
                        'status'   => ['type' => 'status', 'op' => 'is', 'status' => $status],
                        'v_status' => ['type' => 'hidden_status', 'op' => 'is', 'hidden_status' => $v_status],
                    ],
                ]
            );
        } else {
            $topResultHelper = CommunityTopicResults::newFromRequest(
                $this,
                [
                    'specific_terms' => [
                        'brand'  => ['type' => 'brand', 'op' => 'is', 'brand' => $brand_id],
                        'status' => ['type' => 'status', 'op' => 'is', 'status' => $status],
                    ],
                ]
            );
        }

        if ($this->in->getString('subgroup')) {
            $resultHelper = CommunityTopicResults::newFromRequest(
                $this,
                [
                    'specific_terms' => [
                        'brand'   => ['type' => 'brand', 'op' => 'is', 'brand' => $brand_id],
                        'status'  => ['type' => 'status', 'op' => 'is', 'status' => $status],
                        'channel' => [
                            'type'    => 'channel',
                            'op'      => 'is',
                            'channel' => $this->in->getString('subgroup'),
                        ],
                    ],
                ]
            );
        } else {
            $resultHelper = $topResultHelper;
        }

        $grouping = new GroupingCounter();
        $grouping->setGrouping('channel_id');
        $grouping->setIds($topResultHelper->getTopicIds());
        $grouped = $grouping->getDisplayArray();

        return $this->renderList(
            $resultHelper,
            null,
            [
                'list_type'         => 'status',
                'status'            => $status,
                'grouped'           => $grouped,
                'subgroup'          => $this->in->getString('subgroup'),
                'selected_brand_id' => $brand_id,
            ]
        );
    }

    /**
     * This takes a result helper and just handles rendering it.
     *
     * @param CommunityTopicResults $resultsHelper
     * @param string                $template
     * @param array                 $templateVars
     *
     * @return Response
     */
    public function renderList(CommunityTopicResults $resultsHelper, $template = null, array $templateVars = [])
    {
        if (!$template) {
            $template = 'AgentBundle:Community:filter-list.html.twig';
        }

        $result_cache   = $resultsHelper->getResultCache();
        $page           = $this->in->getUInt('p') ?: ($page = $this->in->getUInt('page') ?: 1);
        $communityTopic = $resultsHelper->getTopicsForPage($page);

        if ($this->in->getBool('is_partial')) {
            $template = str_replace('.html.twig', '-part.html.twig', $template);
        }

        $brandId = isset($templateVars['brand_id']) ? $templateVars['brand_id'] : null;

        /** @var CommunityChannelRepository $CommunityChannelRepository */
        /* @var CommunityTopicStatusCategoryRepository $communityTopicStatusCategoryRepository */
        $CommunityChannelRepository             = $this->em->getRepository(CommunityChannel::class);
        $communityTopicStatusCategoryRepository = $this->em->getRepository(CommunityTopicStatusCategory::class);

        // Options for the filter form
        $activeStatusCategories = $communityTopicStatusCategoryRepository->getActiveCategories($brandId);
        $closedStatusCategories = $communityTopicStatusCategoryRepository->getClosedCategories($brandId);
        $communityChannels      = array_filter($CommunityChannelRepository->getFlatHierarchy(), function ($channel) use ($brandId) {
            return $channel['brand_id'] === $brandId;
        });

        $displayFields = $this->person->getPref('agent.ui.community-filter-display-fields.0')
            ?: [
                'date_created',
                'channel',
            ];
        $userCatField = $this->container->getSystemService('CommunityFieldsManager')->getUserCategoryField();

        $communityTopicCollection = new CommunityTopicsCollection(
            $communityTopic,
            $this->container->getEm(),
            $this->container->getSystemService('CommunityFieldsManager')
        );

        $display = $communityTopicCollection->getDisplayArray();

        return $this->render(
            $template,
            array_merge(
                [
                    'display'            => $display,
                    'cache'              => $result_cache,
                    'cache_id'           => $result_cache['id'],
                    'result_ids'         => $result_cache['results'],
                    'community_topic'    => $communityTopic,
                    'num_results'        => $result_cache['num_results'],
                    'per_page'           => 50,
                    'criteria'           => $result_cache['criteria'],
                    'user_cat_field'     => $userCatField,
                    'cur_page'           => $page,
                    'community_channels' => $communityChannels,
                    'active_status_cats' => $activeStatusCategories,
                    'closed_status_cats' => $closedStatusCategories,
                    'display_fields'     => $displayFields,
                ],
                $templateVars
            )
        );
    }

    /**
     * @param $action
     * @param $communityTopicId
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function modifyCommunityTopicApprovementAction($action, $topicId)
    {
        if (!$this->person->hasPerm('agent_publish.validate')) {
            throw $this->createNotFoundException();
        }
        $communityTopic = $this->getTopic($topicId);

        if ($communityTopic) {
            $communityTopicModerate = new CommunityTopicModerate($this->container, $this->person);
            if ($action === 'approve') {
                $communityTopicModerate->approveCommunityTopic($communityTopic);
            } elseif ($action === 'disapprove') {
                $communityTopicModerate->disapproveCommunityTopic($communityTopic, $this->in->getString('reason'));
            }
        }

        $next = $this->getNextValidationCommunityTopic($communityTopic);

        return $this->createJsonResponse(
            [
                'success'  => true,
                'next_url' => $next ? $this->get('object_router')->getAgentPath($next) : null,
            ]
        );
    }

    public function validatingMassActionsAction($action)
    {
        if (!$this->person->hasPerm('agent_publish.validate')) {
            throw $this->createNotFoundException();
        }
        $data = $this->in->getCleanValueArray('content', 'array', 'string');

        foreach ($data as $type => $ids) {
            $reason                 = $this->in->getString('reason');
            $results                = $this->em->getRepository(CommunityTopic::class)->getByIds($ids);
            $communityTopicModerate = new CommunityTopicModerate($this->container, $this->person);
            foreach ($results as $communityTopic) {
                if ($action === 'approve') {
                    $communityTopicModerate->approveCommunityTopic($communityTopic);
                } elseif ($action === 'disapprove') {
                    $communityTopicModerate->disapproveCommunityTopic($communityTopic, $reason);
                }
            }
        }

        return $this->createJsonResponse(['success' => true]);
    }

    /**
     * @param int $topicId
     *
     * @return Response
     */
    public function nextValidatingCommunityTopicAction($topicId)
    {
        $communityTopic = $this->em->find(CommunityTopic::class, $topicId);
        if (!$communityTopic) {
            return $this->createJsonpResponse(['success' => false]);
        }

        $next = $this->getNextValidationCommunityTopic($communityTopic);

        return $this->createJsonResponse(
            [
                'success'  => true,
                'next_url' => $next ? $this->get('object_router')->getAgentUrl($next) : null,
            ]
        );
    }

    /**
     * @param CommunityTopic $current
     *
     * @return CommunityTopic
     */
    private function getNextValidationCommunityTopic(CommunityTopic $current)
    {
        /** @var CommunityTopicRepository $communityTopicRepository */
        $communityTopicRepository = $this->em->getRepository(CommunityTopic::class);
        /** @var CommunityTopic[] $awaitingValidation */
        $awaitingValidation = $communityTopicRepository->getAwaitingValidation(1000);
        while (current($awaitingValidation)) {
            if ($current->getId() !== current($awaitingValidation)->getId()) {
                return current($awaitingValidation);
            }
            next($awaitingValidation);
        }

        return;
    }

    /**
     * @param $action
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Response
     */
    public function massActionsAction($action)
    {
        $this->em->beginTransaction();

        /** @var CommunityTopicRepository $communityTopicRepository */
        $communityTopicRepository = $this->em->getRepository(CommunityTopic::class);
        $communityTopicCollection = $communityTopicRepository->getByIds($this->in->getCleanValueArray('ids', 'uint', 'discard'));

        foreach ($communityTopicCollection as $communityTopic) {
            /* @var CommunityTopic $communityTopic */
            switch ($action) {
                case 'set-status':
                    $communityTopic->setStatusCode($this->in->getString('status'));
                    break;

                case 'set-channel':
                    $channel = $this->em->find('DeskPRO:CommunityChannel', $this->in->getUInt('channel_id'));
                    if ($channel) {
                        $communityTopic->setChannel($channel);
                    }
                    break;
            }
        }

        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse(['success' => 1]);
    }

    //###########################################################################
    // Compare revisions
    //###########################################################################

    /**
     * @param $rev_old_id
     * @param $rev_new_id
     *
     * @return Response
     */
    public function compareRevisionsAction($rev_old_id, $rev_new_id)
    {
        $diff_info = ContentRevisionUtil::compareRevisions('DeskPRO:CommunityTopicRevision', $rev_old_id, $rev_new_id);

        return $this->render(
            'AgentBundle:Community:compare-revs.html.twig',
            [
                'rendered_content_diff' => $diff_info['rendered_content_diff'],
                'rendered_title_diff'   => $diff_info['rendered_title_diff'],
            ]
        );
    }

    //###########################################################################
    // new community topic
    //###########################################################################

    /**
     * @return Response
     */
    public function newCommunityTopicAction()
    {
        $ticket              = null;
        $message             = null;
        $attachments         = [];
        $communityTopiPerson = $this->person;

        if ($this->in->getUInt('ticket_id')) {
            $ticket = $this->getTicket($this->in->getUInt('ticket_id'));

            /** @var \Application\DeskPRO\EntityRepository\TicketMessage $ticketMessageRepo */
            $ticketMessageRepo = $this->em->getRepository(TicketMessage::class);

            if ($this->in->getUInt('message_id')) {
                $message = $ticketMessageRepo->find($this->in->getUInt('message_id'));
            }
            if (!$message || $message->ticket != $ticket) {
                $message = $ticketMessageRepo->getFirstTicketMessage($ticket);
            }

            $communityTopiPerson = $ticket->getPerson();
        }

        if ($message && count($message->attachments)) {
            $storage = $this->container->getBlobStorage();
            foreach ($message->attachments as $attach) {
                try {
                    $newBlob = $storage->createBlobRecordFromString(
                        $storage->copyBlobRecordToString($attach->blob),
                        $attach->blob['filename'],
                        $attach->blob['content_type']
                    );
                } catch (\Exception $ex) {
                    // $ex should be looged internally in services
                    // no need to additional log here
                    continue;
                }
                $this->em->persist($newBlob);

                $attachData                      = [];
                $attachData['blob']              = $newBlob->toArray();
                $attachData['url']               = $newBlob->getDownloadUrl(true);
                $attachData['filesize_readable'] = $newBlob->getReadableFilesize();
                $attachments[]                   = $attachData;
            }
        }

        $selectedBrandId = (int) $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        if (!$selectedBrandId) {
            $selectedBrandId = $this->get('brand_stack')->getDefaultBrand()->getId();
        }

        /** @var PersonPrefRepository $personPrefRepository */
        /* @var CommunityChannelRepository $CommunityChannelRepository */
        /* @var CommunityTopicStatusCategoryRepository $communityTopicStatusCategoryRepository */
        $personPrefRepository                   = $this->em->getRepository(PersonPref::class);
        $CommunityChannelRepository             = $this->em->getRepository(CommunityChannel::class);
        $communityTopicStatusCategoryRepository = $this->em->getRepository(CommunityTopicStatusCategory::class);

        $activeStatusCategories = $communityTopicStatusCategoryRepository->getActiveCategories($selectedBrandId);
        $closedStatusCategories = $communityTopicStatusCategoryRepository->getClosedCategories($selectedBrandId);
        $communityChannels      = array_filter($CommunityChannelRepository->getFlatHierarchy(), function ($channel) use ($selectedBrandId) {
            return $channel['brand_id'] === $selectedBrandId;
        });

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();
        $state  = $personPrefRepository->getPrefForPersonId('agent.ui.state.newcomunitytopic', $this->person->id);

        return $this->render(
            'AgentBundle:Community:new-community-topic.html.twig',
            [
                'ticket'             => $ticket,
                'message'            => $message,
                'topic_person'       => $communityTopiPerson,
                'attachments'        => $attachments,
                'community_channels' => $communityChannels,
                'active_status_cats' => $activeStatusCategories,
                'closed_status_cats' => $closedStatusCategories,
                'state'              => $state,
                'brands'             => $brands,
                'selected_brand_id'  => $selectedBrandId,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function newCommunityTopicSaveAction(Request $request)
    {
        $newCommunityTopic = new NewCommunityTopic(
            $this->getDoctrine()->getManager(),
            $this->person,
            $this->get('ticket_manager'),
            $this->get('community_subscription_helper')
        );

        $formType = new NewCommunityTopicTypeOld();
        $form     = $this->get('form.factory')->create($formType, $newCommunityTopic);

        if ($request->getMethod() == 'POST') {
            $data            = $request->get($form->getName()) ?: [];
            $data['content'] = $this->person->hasPerm('agent_publish.can_insert_html')
                ? $this->in->getCleanValue($form->getName().'.content', 'string', null, ['noclean' => true])
                : $this->in->getCleanValue($form->getName().'.content', 'html');

            $form->submit($data);
            $form->isValid();

            $validator = new NewCommunityTopicValidator();
            if (!$validator->isValid($newCommunityTopic)) {
                return $this->createJsonResponse(
                    [
                        'error'       => true,
                        'error_codes' => $validator->getErrorGroups(),
                    ]
                );
            }

            $newCommunityTopic->save();
            $communityTopic = $newCommunityTopic->getTopic();

            /** @var PersonPrefRepository $personPrefRepository */
            $personPrefRepository = $this->em->getRepository('DeskPRO:PersonPref');
            $personPrefRepository->deletePrefForPersonId(
                'agent.ui.state.newcomunitytopic',
                $this->person->id
            );

            if ($communityTopic->getPerson()->getId() !== $this->person->getId()) {
                $this->_sendAgentCreatedCommunityTopicForUserNotification($communityTopic);
            }

            return $this->createJsonResponse(
                [
                    'success'             => true,
                    'community_topic_id'  => $communityTopic['id'],
                    'community_topic_url' => $this->get('object_router')->getPortalUrl($communityTopic),
                ]
            );
        } else {
            return $this->createJsonResponse(
                [
                    'success' => false,
                ]
            );
        }
    }

    protected function _sendAgentCreatedCommunityTopicForUserNotification(CommunityTopic $communityTopic)
    {
        if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            //TODO previously here was the call of a unexisting method
            // UserViewModelFactory::createAgentCreatedNewFeedbackForUser
            $viewModel = $this->get('email.user_viewmodel_factory')
                ->createCommunityTopicNewModel($communityTopic);
            $this->get('email.email_sender')
                ->send($viewModel, ['to' => $communityTopic->getPerson()->getEmail()]);
        } else {
            $message = $this->container->getMailer()->createMessage();
            $message->setTo(
                $communityTopic->getPerson()->getEmail(),
                $communityTopic->getPerson()->getDisplayName()
            );
            // unexisting template also
            $message->setTemplate('DeskPRO:emails_user:new-community-topic-created-for-user.html.twig', [
                'topic' => $communityTopic,
            ]);
            $this->container->getMailer()->send($message);
        }
    }

    /**
     * @param $communityTopicId
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return CommunityTopic
     */
    private function getTopic($communityTopicId)
    {
        if (!$communityTopic = $this->em->find(CommunityTopic::class, $communityTopicId)) {
            throw $this->createNotFoundException();
        }

        return $communityTopic;
    }

    /**
     * @param $ticketId
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return Ticket
     */
    private function getTicket($ticketId)
    {
        if (!$ticket = $this->em->find(Ticket::class, $ticketId)) {
            throw $this->createNotFoundException(sprintf('There is no ticket with ID %s', $ticketId));
        }

        return $ticket;
    }

    /**
     * @param CommunityTopic $communityTopic
     *
     * @return null|Response
     */
    private function checkPermissions(CommunityTopic $communityTopic)
    {
        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if (!$publishChecker->canEdit($communityTopic)) {
            return $this->createJsonResponse(['success' => false]);
        }

        return;
    }
}
