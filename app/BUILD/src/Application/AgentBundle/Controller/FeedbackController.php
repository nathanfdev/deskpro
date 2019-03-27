<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\FeedbackResults;
use Application\AgentBundle\Form\Model\NewFeedback;
use Application\AgentBundle\Form\Type\NewFeedback as NewFeedbackTypeOld;
use Application\AgentBundle\Validator\NewFeedbackValidator;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Entity\SearchStickyResult;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\Feedback as FeedbackRepository;
use Application\DeskPRO\EntityRepository\FeedbackCategory as FeedbackCategoryRepository;
use Application\DeskPRO\EntityRepository\FeedbackComment as FeedbackCommentRepository;
use Application\DeskPRO\EntityRepository\FeedbackStatusCategory as FeedbackStatusCategoryRepository;
use Application\DeskPRO\EntityRepository\PersonPref as PersonPrefRepository;
use Application\DeskPRO\EntityRepository\SearchLog as SearchLogRepository;
use Application\DeskPRO\EntityRepository\SearchStickyResult as SearchStickyResultRepository;
use Application\DeskPRO\Feedback\FeedbackCollection;
use Application\DeskPRO\Feedback\FeedbackMerge;
use Application\DeskPRO\Feedback\FeedbackModerate;
use Application\DeskPRO\Labels\LabelLister;
use Application\DeskPRO\People\PermissionChecker\PublishChecker;
use Application\DeskPRO\Publish\Feedback\GroupingCounter;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use DeskPRO\Bundle\AppBundle\Entity\TicketFeedbackLink;
use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkGenerator\FeedbackLinkGenerator;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles ticket searches.
 */
class FeedbackController extends AbstractController
{
    //###########################################################################
    // get-section-data
    //###########################################################################

    /**
     * @return Response
     */
    public function getSectionDataAction()
    {
        $selectedBrandId = $this->in->getUInt('brand_id');
        if (!$selectedBrandId) {
            $selectedBrandId = (int) $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }
        if (!$selectedBrandId) {
            $selectedBrandId = $this->get('brand_stack')->getDefaultBrand()->getId();
        }

        /** @var FeedbackRepository $feedbackRepository */
        /* @var FeedbackCategoryRepository $feedbackCategoryRepository */
        /* @var FeedbackStatusCategoryRepository $feedbackStatusCategoryRepository */
        /* @var FeedbackCommentRepository $feedbackCommentRepository */
        $feedbackRepository               = $this->em->getRepository(Feedback::class);
        $feedbackCategoryRepository       = $this->em->getRepository(FeedbackCategory::class);
        $feedbackStatusCategoryRepository = $this->em->getRepository(FeedbackStatusCategory::class);
        $feedbackCommentRepository        = $this->em->getRepository(FeedbackComment::class);

        $counts = [
            'feedback_awaiting_validation' => $feedbackRepository->countAwaitingValidation(),
            'comments_awaiting_validation' => $feedbackCommentRepository->countAwaitingValidation(),
        ];

        $statusCounts = [
            'active' => $feedbackRepository->countActiveGrouped($selectedBrandId),
            'closed' => $feedbackRepository->countClosedGrouped($selectedBrandId),
            'hidden' => $feedbackRepository->countHiddenGrouped($selectedBrandId),
        ];

        $categoryCounts = $feedbackRepository->countAllCategoriesGrouped();

        $activeStatusCategories = $feedbackStatusCategoryRepository->getActiveCategories($selectedBrandId);
        $closedStatusCategories = $feedbackStatusCategoryRepository->getClosedCategories($selectedBrandId);
        $feedbackCategories     = array_filter($feedbackCategoryRepository->getFlatHierarchy(), function ($category) use ($selectedBrandId) {
            return $category['brand_id'] === $selectedBrandId;
        });

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();

        $labelLister      = new LabelLister('feedback');
        $feedbackTagIndex = $labelLister->getIndexList();

        $data = [
            'section_html' => $this->renderView(
                'AgentBundle:Feedback:window-section.html.twig',
                [
                    'counts'             => $counts,
                    'status_counts'      => $statusCounts,
                    'category_counts'    => $categoryCounts,
                    'feedback_cats'      => $feedbackCategories,
                    'active_status_cats' => $activeStatusCategories,
                    'closed_status_cats' => $closedStatusCategories,
                    'feedback_tag_index' => $feedbackTagIndex,
                    'brands'             => $brands,
                    'selected_brand_id'  => $selectedBrandId,
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

        /** @var \Application\DeskPRO\EntityRepository\Feedback $feedbackRepository */
        $feedbackRepository = $this->em->getRepository(Feedback::class);
        $pageinfo           = $total           = null;

        $currentPage = $this->in->getUInt('page') ?: 1;
        $offset      = (($currentPage - 1 >= 0) ? $currentPage - 1 : 1) * $perPage;

        if (!@$_REQUEST['_partial']) {
            $total    = $feedbackRepository->countAwaitingValidation();
            $pageinfo = Numbers::getPaginationPages($total, $currentPage, $perPage);
        }

        $contentValidating = $feedbackRepository->getAwaitingValidation($perPage, $offset);
        $info              = [];
        foreach ($contentValidating as $feedback) {
            /* @var Feedback $feedback */
            $lastRevision = $feedback->getRevisions()->current();
            $info[]       = [
                'info' => [
                    'content_type' => 'feedback',
                    'content_id'   => $feedback->getId(),
                    'revision_id'  => $lastRevision ? $lastRevision->getId() : null,
                    'date_created' => $feedback->getDateCreated()->format('Y-m-d H:i:s'),
                ],
                'obj' => $feedback,
            ];
        }

        $tpl = @$_REQUEST['_partial']
            ? 'AgentBundle:Publish:validating-content-page.html.twig'
            : 'AgentBundle:Publish:validating-content.html.twig'
        ;

        return $this->render(
            $tpl,
            [
                'single_type'        => 'feedback',
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
     * @param $feedback_id
     *
     * @return Response
     */
    public function viewAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        /** @var PublishChecker $publishChecker */
        /* @var SearchLogRepository $searchLogRepository */
        /* @var PersonPrefRepository $personPrefRepository */
        /* @var FeedbackCategoryRepository $feedbackCategoryRepository */
        /* @var SearchStickyResultRepository $searchStickyResultRepository */
        /* @var FeedbackStatusCategoryRepository $feedbackStatusCategoryRepository */
        $publishChecker                   = $this->person->getPermissionsManager()->get('PublishChecker');
        $fieldManager                     = $this->container->getSystemService('feedback_fields_manager');
        $searchLogRepository              = $this->em->getRepository(SearchLog::class);
        $personPrefRepository             = $this->em->getRepository(PersonPref::class);
        $feedbackCategoryRepository       = $this->em->getRepository(FeedbackCategory::class);
        $searchStickyResultRepository     = $this->em->getRepository(SearchStickyResult::class);
        $feedbackStatusCategoryRepository = $this->em->getRepository(FeedbackStatusCategory::class);

        $customFields        = $fieldManager->getDisplayArrayForObject($feedback);
        $feedbackComments    = [];
        $feedbackCommentsRaw = $feedback->getComments();

        foreach ($feedbackCommentsRaw as $c) {
            if ($c->status != 'temp') {
                $feedbackComments[] = $c;
            }
        }

        $state        = $personPrefRepository->getPrefForPersonId('agent.ui.state.editfeedback', $this->person->id);
        $category     = $feedback->getCategory();
        $categoryPath = $category->getTreeParents();

        $ratedSearches          = $searchLogRepository->getRatedSearchesFor('feedback', $feedback['id'], 'counted');
        $relatedFinder          = new RelatedContentFinder($this->person, $feedback);
        $relatedContent         = $relatedFinder->getRelatedEntities(true);
        $feedbackRevisions      = $feedback->getRevisions();
        $stickySearchWords      = $searchStickyResultRepository->getWordsForObject($feedback);
        $activeStatusCategories = $feedbackStatusCategoryRepository->getActiveCategories($feedback->getBrand());
        $closedStatusCategories = $feedbackStatusCategoryRepository->getClosedCategories($feedback->getBrand());
        $feedbackCategories     = array_filter($feedbackCategoryRepository->getInHierarchy(), function ($category) use ($feedback) {
            return $feedback->getBrand() && $category['brand_id'] === $feedback->getBrand()->getId();
        });

        //@TODO: related entities fetching optimization
        $feedbackRepo        = $this->em->getRepository(TicketFeedbackLink::class);
        $ticketFeedbackLinks = $feedbackRepo->findByFeedback($feedback);

        //@TODO: select only needed data to display persons
        $subscribedIds = $this->em->getRepository('DeskPRO:FeedbackSubscription')->getSubscribedPersonIds($feedback);
        // limit to 250
        $subscribedIds     = array_slice($subscribedIds, 0, 250);
        $subscribedPersons = $this->em->getRepository('DeskPRO:Person')->findById($subscribedIds);

        $perms = [
            'can_edit'   => $publishChecker->canEdit($feedback),
            'can_delete' => $publishChecker->canDelete($feedback),
        ];

        return $this->render(
            'AgentBundle:Feedback:view.html.twig',
            [
                'feedback'              => $feedback,
                'feedback_comments'     => $feedbackComments,
                'feedback_revisions'    => $feedbackRevisions,
                'state'                 => $state,
                'category'              => $category,
                'category_path'         => $categoryPath,
                'custom_fields'         => $customFields,
                'rated_searches'        => $ratedSearches,
                'related_content'       => $relatedContent,
                'sticky_search_words'   => $stickySearchWords,
                'feedback_categories'   => $feedbackCategories,
                'active_status_cats'    => $activeStatusCategories,
                'closed_status_cats'    => $closedStatusCategories,
                'ticket_feedback_links' => $ticketFeedbackLinks,
                'subscribed_persons'    => $subscribedPersons,
                'perms'                 => $perms,
                'permalink'             => $this->get('object_router')
                    ->getPortalUrl($feedback, FeedbackLinkGenerator::TYPE_PERMALINK),
            ]
        );
    }

    /**
     * @param $feedback_id
     *
     * @return Response
     */
    public function whoVotedAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        $feedback_votes = $feedback->votes->toArray();

        return $this->render(
            'AgentBundle:Feedback:view-who-voted.html.twig',
            [
                'feedback'       => $feedback,
                'feedback_votes' => $feedback_votes,
            ]
        );
    }

    public function ajaxGetCategoriesByBrandAction($brand_id)
    {
        $feedbackCategories = array_filter($this->em->getRepository(FeedbackCategory::class)->getFlatHierarchy(), function ($category) use ($brand_id) {
            return $category['brand_id'] === (int) $brand_id;
        });

        return $this->render('AgentBundle:Common:select-standard.html.twig', [
            'name'             => 'newfeedback[category_id]',
            'id'               => '_cat',
            'add_classname'    => 'category_id',
            'add_attr'         => '',
            'with_blank'       => 0,
            'blank_title'      => '',
            'categories'       => $feedbackCategories,
            'allow_parent_sel' => true,
        ]);
    }

    public function ajaxGetStatusesByBrandAction($brand_id)
    {
        $feedbackStatusCategoryRepository = $this->em->getRepository(FeedbackStatusCategory::class);

        $activeStatusCategories = $feedbackStatusCategoryRepository->getActiveCategories($brand_id);
        $closedStatusCategories = $feedbackStatusCategoryRepository->getClosedCategories($brand_id);

        return $this->render('AgentBundle:Common:select-feedback-status.html.twig', [
            'name'               => 'newfeedback[status_code]',
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
     * @param $feedback_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function ajaxSaveEditablesAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if (!$publishChecker->canEdit($feedback)) {
            return $this->createJsonResponse(['success' => false]);
        }

        $ret = '';

        switch ($this->in->getString('action')) {
            case 'title':
                $value             = $this->in->getString('title');
                $feedback['title'] = $value;
                $ret               = ['html' => htmlspecialchars($feedback['title'])];
                break;
        }

        $this->em->transactional(
            function ($em) use ($feedback) {
                /* @var EntityManager $em */
                $em->persist($feedback);
                $em->flush();
            }
        );

        return $this->createJsonResponse(
            [
                'success'     => true,
                'feedback_id' => $feedback->getId(),
                'html'        => $ret,
            ]
        );
    }

    /**
     * @param $feedback_id
     * @param $category_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return Response
     */
    public function ajaxUpdateCategoryAction($feedback_id, $category_id)
    {
        $feedback = $this->getFeedback($feedback_id);
        $cat      = $this->em->find(FeedbackCategory::class, $category_id);
        $feedback->setCategory($cat);

        $this->em->transactional(
            function ($em) use ($feedback) {
                /* @var EntityManager $em */
                $em->persist($feedback);
                $em->flush();
            }
        );

        return $this->createJsonResponse(
            [
                'success'     => true,
                'feedback_id' => $feedback['id'],
            ]
        );
    }

    /**
     * @param $feedback_id
     * @param $status_code
     *
     * @throws \Exception
     *
     * @return null|Response
     */
    public function ajaxUpdateStatusAction($feedback_id, $status_code)
    {
        $feedback = $this->getFeedback($feedback_id);

        if ($response = $this->checkPermissions($feedback)) {
            return $response;
        }

        $feedback->setStatusCode($status_code);
        $this->em->transactional(
            function ($em) use ($feedback) {
                /* @var EntityManager $em */
                $em->persist($feedback);
                $em->flush();
            }
        );

        return $this->createJsonResponse(
            [
                'success'     => true,
                'feedback_id' => $feedback->getId(),
            ]
        );
    }

    /**
     * @param $feedback_id
     *
     * @throws \Exception
     *
     * @return null|Response
     */
    public function ajaxSaveCustomFieldsAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        if ($response = $this->checkPermissions($feedback)) {
            return $response;
        }

        $this->em->beginTransaction();

        try {
            $fieldManager     = $this->container->getSystemService('feedback_fields_manager');
            $postCustomFields = $this->request->request->get('custom_fields', []);
            if (!empty($postCustomFields)) {
                $fieldManager->saveFormToObject($postCustomFields, $feedback);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $customFields = $fieldManager->getDisplayArrayForObject($feedback);

        return $this->render(
            'AgentBundle:Feedback:view-customfields-rendered-rows.html.twig',
            [
                'feedback'      => $feedback,
                'custom_fields' => $customFields,
            ]
        );
    }

    /**
     * @param $feedback_id
     *
     * @return null|Response
     */
    public function ajaxSaveLabelsAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        if ($response = $this->checkPermissions($feedback)) {
            return $response;
        }

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $feedback->getLabelManager()->setLabelsArray($labels);
        $this->em->persist($feedback);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    /**
     * @param $feedback_id
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     *
     * @return Response
     */
    public function ajaxSaveCommentAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        if (!$this->in->getString('content')) {
            throw $this->createNotFoundException();
        }

        $comment = new FeedbackComment();
        $comment
            ->setIsReviewed(true)
            ->setContent($this->in->getString('content'))
            ->setStatus($this->in->getBool('agent_only') ? 'agent' : 'visible');

        $feedback->addComment($comment);
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
            'AgentBundle:Feedback:view-comment.html.twig',
            [
                'comment' => $comment,
            ]
        );
    }

    /**
     * @param $feedback_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function ajaxSaveAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        $rev    = null;
        $action = $this->in->getString('action');

        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if ($action == 'delete' && !$publishChecker->canDelete($feedback)) {
            return $this->createJsonResponse(['success' => false]);
        } elseif ($response = $this->checkPermissions($feedback)) {
            return $response;
        }

        $data = ['success' => 1];

        $this->em->beginTransaction();

        switch ($action) {
            case 'status':
            case 'delete':
                if ($action == 'delete') {
                    $feedback['status_code'] = 'hidden.deleted';
                } else {
                    $feedback['status_code'] = $this->in->getString('status');
                }
                break;

            case 'undelete':
                $feedback['status_code'] = 'new';
                break;

            case 'title':
                $feedback['title'] = $this->in->getString('title');
                $rev               = ContentRevisionUtil::findOrCreate($feedback, 'title', $this->person);
                $rev['title']      = $feedback['title'];
                break;

            case 'slug':
                $feedback['slug'] = Strings::slugifyTitle($this->in->getString('slug')) ?: 'view';
                $data['slug']     = $feedback['slug'];
                break;

            case 'add-related':
                $updater = new RelatedContentUpdate($feedback);
                $updater->addRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-related':
                $updater = new RelatedContentUpdate($feedback);
                $updater->removeRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-blob':
                foreach ($feedback->getAttachments() as $k => $attach) {
                    if ($attach->blob['id'] == $this->in->getUInt('blob_id')) {
                        $feedback->getAttachments()->remove($k);
                        $this->em->remove($attach);
                        break;
                    }
                }

                break;

            case 'content':
                /** @var PersonPrefRepository $personPrefRepository */
                $personPrefRepository = $this->em->getRepository('DeskPRO:PersonPref');
                $personPrefRepository->deletePrefForPersonId('agent.ui.state.editfeedback', $this->person->id);

                $content = $this->person->hasPerm('agent_publish.can_insert_html')
                    ? $this->in->getCleanValue('content', 'string', null, ['noclean' => true])
                    : $this->in->getCleanValue('content', 'html');

                $feedback->setContent($content);

                $inlineBlobIds = $this->in->getCleanValueArray('blob_inline_ids', 'int');
                $this->get('attachment_helper')->processInlineBlobs($content, $inlineBlobIds);

                $data['content_html'] = $this->renderView(
                    'AgentBundle:Feedback:view-content-tab.html.twig',
                    ['feedback' => $feedback]
                );

                $rev            = ContentRevisionUtil::findOrCreate($feedback, ['content'], $this->person);
                $rev['content'] = $feedback['content'];

                break;

            case 'category':
                $cat = $this->em->find('DeskPRO:FeedbackCategory', $this->in->getUInt('category_id'));
                if ($cat) {
                    $feedback['category'] = $cat;
                    $data['category_id']  = $cat['id'];
                }
                break;
        }

        $this->em->persist($feedback);
        foreach ($feedback->getAttachments() as $attachment) {
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

        return $this->createJsonResponse($data);
    }

    /**
     * @param $feedback_id
     *
     * @return Response
     */
    public function ajaxSubscribePersonAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        if (!$person = $this->em->find(Person::class, $this->in->getUInt('person_id'))) {
            throw $this->createNotFoundException(sprintf(
                'There is no person with ID %s',
                $this->in->getUInt('person_id')
            ));
        }

        $this->get('feedback_subscription_helper')->subscribePersons($feedback, [$person]);

        return $this->createJsonResponse(['success' => 1]);
    }

    /**
     * @param $feedback_id
     *
     * @return Response
     */
    public function ajaxUnsubscribePersonAction($feedback_id)
    {
        $feedback = $this->getFeedback($feedback_id);

        if (!$person = $this->em->find(Person::class, $this->in->getUInt('person_id'))) {
            throw $this->createNotFoundException(sprintf(
                'There is no person with ID %s',
                $this->in->getUInt('person_id')
            ));
        }

        $this->get('feedback_subscription_helper')->unsubscribePerson($feedback, $person);

        return $this->createJsonResponse(['success' => 1]);
    }

    //###########################################################################
    // merge
    //###########################################################################

    /**
     * @param     $feedback_id
     * @param int $other_feedback_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function mergeOverlayAction($feedback_id, $other_feedback_id = 0)
    {
        $feedback = $this->em->find(Feedback::class, $feedback_id);

        if ($other_feedback_id && $other_feedback_id != $feedback_id) {
            $otherFeedback = $this->em->find(Feedback::class, $other_feedback_id);
        } else {
            $otherFeedback = false;
        }

        return $this->render(
            'AgentBundle:Feedback:merge-overlay.html.twig',
            [
                'feedback'       => $feedback,
                'other_feedback' => $otherFeedback,
            ]
        );
    }

    /**
     * Merge a ticket interface.
     *
     * @param $feedback_id
     * @param $other_feedback_id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function mergeAction($feedback_id, $other_feedback_id)
    {
        $feedback      = $this->getFeedback($feedback_id);
        $otherFeedback = $this->getFeedback($other_feedback_id);

        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if (!$publishChecker->canEdit($feedback)
            || !$publishChecker->canEdit($otherFeedback)
            || !$publishChecker->canDelete($otherFeedback)
        ) {
            return $this->createJsonResponse(['success' => false]);
        }

        $old_feedback_id = $otherFeedback['id'];

        try {
            $this->em->beginTransaction();
            $merge = new FeedbackMerge($this->person, $feedback, $otherFeedback);
            $merge->merge();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $this->createJsonResponse(
            [
                'success' => true,
                'id'      => $feedback->getId(),
                'old_id'  => $old_feedback_id,
            ]
        );
    }

    //###########################################################################
    // filters
    //###########################################################################

    /**
     * Any general search. For example, status, category or label.
     *
     * @return Response
     */
    public function filterListAction()
    {
        $resultHelper = FeedbackResults::newFromRequest($this);

        return $this->renderList(
            $resultHelper,
            null,
            ['list_type' => 'filter']
        );
    }

    /**
     * A shortcut to run a filter on a category.
     *
     * @param  $category_id
     *
     * @return Response
     */
    public function categoryListAction($category_id)
    {
        $topResultHelper = FeedbackResults::newFromRequest(
            $this,
            [
                'specific_terms' => [
                    'category' => ['type' => 'category', 'op' => 'is', 'category' => $category_id],
                    'status'   => ['type' => 'status', 'op' => 'not', 'status' => 'hidden'],
                ],
            ]
        );

        if ($this->in->getString('subgroup')) {
            $resultHelper = FeedbackResults::newFromRequest(
                $this,
                [
                    'specific_terms' => [
                        'category' => ['type' => 'category', 'op' => 'is', 'category' => $category_id],
                        'status'   => ['type' => 'status', 'op' => 'is', 'status' => $this->in->getString('subgroup')],
                    ],
                ]
            );
        } else {
            $resultHelper = $topResultHelper;
        }

        $cat = $this->em->find('DeskPRO:FeedbackCategory', $category_id);

        $grouping = new GroupingCounter();
        $grouping->setGrouping('status')->setIds($topResultHelper->getFeedbackIds());
        $grouped      = $grouping->getDisplayArray();
        $grouped_info = [];

        if (!$cat->parent) {
            $grouped_key = $cat->getId();

            $t = 0;
            if (isset($grouped['items'][$grouped_key])) {
                $grouped_info = Arrays::mergeAssoc($grouped_info, [$grouped_key => $grouped['items'][$grouped_key]]);
                $t            = $grouped['items'][$grouped_key]['total'];
            }

            $grouped_info[-1] = ['id' => -1, 'title' => 'TOTAL', 'total' => $t];
        } else {
            $grouped_key = $cat->getId();
            $t           = 0;
            foreach ($cat->children as $c) {
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
                'list_type'    => 'category',
                'brand_id'     => $cat->getBrand() ? $cat->getBrand()->getId() : null,
                'category_id'  => $category_id,
                'page_title'   => $cat->getFullTitle(),
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
        $resultHelper = FeedbackResults::newFromRequest(
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
            $topResultHelper         = FeedbackResults::newFromRequest(
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
            $topResultHelper = FeedbackResults::newFromRequest(
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
            $resultHelper = FeedbackResults::newFromRequest(
                $this,
                [
                    'specific_terms' => [
                        'brand'    => ['type' => 'brand', 'op' => 'is', 'brand' => $brand_id],
                        'status'   => ['type' => 'status', 'op' => 'is', 'status' => $status],
                        'category' => [
                            'type'     => 'category',
                            'op'       => 'is',
                            'category' => $this->in->getString('subgroup'),
                        ],
                    ],
                ]
            );
        } else {
            $resultHelper = $topResultHelper;
        }

        $grouping = new GroupingCounter();
        $grouping->setGrouping('category_id');
        $grouping->setIds($topResultHelper->getFeedbackIds());
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
     * @param FeedbackResults $resultsHelper
     * @param string          $template
     * @param array           $templateVars
     *
     * @return Response
     */
    public function renderList(FeedbackResults $resultsHelper, $template = null, array $templateVars = [])
    {
        if (!$template) {
            $template = 'AgentBundle:Feedback:filter-list.html.twig';
        }

        $result_cache = $resultsHelper->getResultCache();
        $page         = $this->in->getUInt('p') ?: ($page = $this->in->getUInt('page') ?: 1);
        $feedback     = $resultsHelper->getFeedbackForPage($page);

        if ($this->in->getBool('is_partial')) {
            $template = str_replace('.html.twig', '-part.html.twig', $template);
        }

        $brandId = isset($templateVars['brand_id']) ? $templateVars['brand_id'] : null;

        /** @var FeedbackCategoryRepository $feedbackCategoryRepository */
        /* @var FeedbackStatusCategoryRepository $feedbackStatusCategoryRepository */
        $feedbackCategoryRepository       = $this->em->getRepository(FeedbackCategory::class);
        $feedbackStatusCategoryRepository = $this->em->getRepository(FeedbackStatusCategory::class);

        // Options for the filter form
        $activeStatusCategories = $feedbackStatusCategoryRepository->getActiveCategories($brandId);
        $closedStatusCategories = $feedbackStatusCategoryRepository->getClosedCategories($brandId);
        $feedbackCategories     = array_filter($feedbackCategoryRepository->getFlatHierarchy(), function ($category) use ($brandId) {
            return $category['brand_id'] === $brandId;
        });

        $displayFields = $this->person->getPref('agent.ui.feedback-filter-display-fields.0')
            ?: [
                'date_created',
                'category',
            ];
        $userCatField = $this->container->getSystemService('FeedbackFieldsManager')->getUserCategoryField();

        $feedback_collection = new FeedbackCollection(
            $feedback,
            $this->container->getEm(),
            $this->container->getSystemService('FeedbackFieldsManager')
        );

        $display = $feedback_collection->getDisplayArray();

        return $this->render(
            $template,
            array_merge(
                [
                    'display'            => $display,
                    'cache'              => $result_cache,
                    'cache_id'           => $result_cache['id'],
                    'result_ids'         => $result_cache['results'],
                    'feedback'           => $feedback,
                    'num_results'        => $result_cache['num_results'],
                    'per_page'           => 50,
                    'criteria'           => $result_cache['criteria'],
                    'user_cat_field'     => $userCatField,
                    'cur_page'           => $page,
                    'feedback_cats'      => $feedbackCategories,
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
     * @param $feedbackId
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function modifyFeedbackApprovementAction($action, $feedbackId)
    {
        if (!$this->person->hasPerm('agent_publish.validate')) {
            throw $this->createNotFoundException();
        }
        $feedback = $this->getFeedback($feedbackId);

        if ($feedback) {
            $feedbackModerate = new FeedbackModerate($this->container, $this->person);
            if ($action === 'approve') {
                $feedbackModerate->approveFeedback($feedback);
            } elseif ($action === 'disapprove') {
                $feedbackModerate->disapproveFeedback($feedback, $this->in->getString('reason'));
            }
        }

        $next = $this->getNextValidationFeedback($feedback);

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
            $reason           = $this->in->getString('reason');
            $results          = $this->em->getRepository(Feedback::class)->getByIds($ids);
            $feedbackModerate = new FeedbackModerate($this->container, $this->person);
            foreach ($results as $feedback) {
                if ($action === 'approve') {
                    $feedbackModerate->approveFeedback($feedback);
                } elseif ($action === 'disapprove') {
                    $feedbackModerate->disapproveFeedback($feedback, $reason);
                }
            }
        }

        return $this->createJsonResponse(['success' => true]);
    }

    /**
     * @param int $feedbackId
     *
     * @return Response
     */
    public function nextValidatingFeedbackAction($feedbackId)
    {
        $feedback = $this->em->find(Feedback::class, $feedbackId);
        if (!$feedback) {
            return $this->createJsonpResponse(['success' => false]);
        }

        $next = $this->getNextValidationFeedback($feedback);

        return $this->createJsonResponse(
            [
                'success'  => true,
                'next_url' => $next ? $this->get('object_router')->getAgentUrl($next) : null,
            ]
        );
    }

    /**
     * @param Feedback $current
     *
     * @return Feedback
     */
    private function getNextValidationFeedback(Feedback $current)
    {
        /** @var FeedbackRepository $feedbackRepository */
        $feedbackRepository = $this->em->getRepository(Feedback::class);
        /** @var Feedback[] $awaitingValidation */
        $awaitingValidation = $feedbackRepository->getAwaitingValidation(1000);
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function massActionsAction($action)
    {
        $this->em->beginTransaction();

        /** @var FeedbackRepository $feedbackRepository */
        $feedbackRepository = $this->em->getRepository(Feedback::class);
        $feedbackCollection = $feedbackRepository->getByIds($this->in->getCleanValueArray('ids', 'uint', 'discard'));

        foreach ($feedbackCollection as $feedback) {
            /* @var Feedback $feedback */
            switch ($action) {
                case 'set-status':
                    $feedback->setStatusCode($this->in->getString('status'));
                    break;

                case 'set-category':
                    $cat = $this->em->find('DeskPRO:FeedbackCategory', $this->in->getUInt('category_id'));
                    if ($cat) {
                        $feedback->setCategory($cat);
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
        $diff_info = ContentRevisionUtil::compareRevisions('DeskPRO:FeedbackRevision', $rev_old_id, $rev_new_id);

        return $this->render(
            'AgentBundle:Feedback:compare-revs.html.twig',
            [
                'rendered_content_diff' => $diff_info['rendered_content_diff'],
                'rendered_title_diff'   => $diff_info['rendered_title_diff'],
            ]
        );
    }

    //###########################################################################
    // newfeedback
    //###########################################################################

    /**
     * @return Response
     */
    public function newFeedbackAction()
    {
        $ticket          = null;
        $message         = null;
        $attachments     = [];
        $feedback_person = $this->person;

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

            $feedback_person = $ticket->getPerson();
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
         /* @var FeedbackCategoryRepository       $feedbackCategoryRepository */
         /* @var FeedbackStatusCategoryRepository $feedbackStatusCategoryRepository */
        $personPrefRepository             = $this->em->getRepository(PersonPref::class);
        $feedbackCategoryRepository       = $this->em->getRepository(FeedbackCategory::class);
        $feedbackStatusCategoryRepository = $this->em->getRepository(FeedbackStatusCategory::class);

        $activeStatusCategories = $feedbackStatusCategoryRepository->getActiveCategories($selectedBrandId);
        $closedStatusCategories = $feedbackStatusCategoryRepository->getClosedCategories($selectedBrandId);
        $feedbackCategories     = array_filter($feedbackCategoryRepository->getFlatHierarchy(), function ($category) use ($selectedBrandId) {
            return $category['brand_id'] === $selectedBrandId;
        });

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();
        $state  = $personPrefRepository->getPrefForPersonId('agent.ui.state.newfeedback', $this->person->id);

        return $this->render(
            'AgentBundle:Feedback:newfeedback.html.twig',
            [
                'ticket'              => $ticket,
                'message'             => $message,
                'feedback_person'     => $feedback_person,
                'attachments'         => $attachments,
                'feedback_categories' => $feedbackCategories,
                'active_status_cats'  => $activeStatusCategories,
                'closed_status_cats'  => $closedStatusCategories,
                'state'               => $state,
                'brands'              => $brands,
                'selected_brand_id'   => $selectedBrandId,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function newFeedbackSaveAction(Request $request)
    {
        $newfeedback = new NewFeedback(
            $this->getDoctrine()->getManager(),
            $this->person,
            $this->get('ticket_manager'),
            $this->get('feedback_subscription_helper')
        );

        $formType = new NewFeedbackTypeOld();
        $form     = $this->get('form.factory')->create($formType, $newfeedback);

        if ($request->getMethod() == 'POST') {
            $data            = $request->get($form->getName()) ?: [];
            $data['content'] = $this->person->hasPerm('agent_publish.can_insert_html')
                ? $this->in->getCleanValue($form->getName().'.content', 'string', null, ['noclean' => true])
                : $this->in->getCleanValue($form->getName().'.content', 'html');

            $form->submit($data);
            $form->isValid();

            $validator = new NewFeedbackValidator();
            if (!$validator->isValid($newfeedback)) {
                return $this->createJsonResponse(
                    [
                        'error'       => true,
                        'error_codes' => $validator->getErrorGroups(),
                    ]
                );
            }

            $newfeedback->save();
            $feedback = $newfeedback->getFeedback();

            /** @var PersonPrefRepository $personPrefRepository */
            $personPrefRepository = $this->em->getRepository('DeskPRO:PersonPref');
            $personPrefRepository->deletePrefForPersonId(
                'agent.ui.state.newfeedback',
                $this->person->id
            );

            if ($feedback->getPerson()->getId() !== $this->person->getId()) {
                $this->_sendAgentCreatedFeedbackForUserNotification($feedback);
            }

            return $this->createJsonResponse(
                [
                    'success'      => true,
                    'feedback_id'  => $feedback['id'],
                    'feedback_url' => $this->get('object_router')->getPortalUrl($feedback),
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

    protected function _sendAgentCreatedFeedbackForUserNotification(Feedback $feedback)
    {
        if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = $this->get('email.user_viewmodel_factory')
                ->createAgentCreatedNewFeedbackForUserModel($feedback);
            $this->get('email.email_sender')
                ->send($viewModel, ['to' => $feedback->getPerson()->getEmail()]);
        } else {
            $message = $this->container->getMailer()->createMessage();
            $message->setTo(
                $feedback->getPerson()->getEmail(),
                $feedback->getPerson()->getDisplayName()
            );
            $message->setTemplate('DeskPRO:emails_user:new-feedback-created-for-user.html.twig', [
                    'feedback' => $feedback,
            ]);
            $this->container->getMailer()->send($message);
        }
    }

    /**
     * @param $feedbackId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Feedback
     */
    private function getFeedback($feedbackId)
    {
        if (!$feedback = $this->em->find(Feedback::class, $feedbackId)) {
            throw $this->createNotFoundException();
        }

        return $feedback;
    }

    /**
     * @param $ticketId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
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
     * @param Feedback $feedback
     *
     * @return null|Response
     */
    private function checkPermissions(Feedback $feedback)
    {
        /** @var PublishChecker $publishChecker */
        $publishChecker = $this->person->getPermissionsManager()->get('PublishChecker');

        if (!$publishChecker->canEdit($feedback)) {
            return $this->createJsonResponse(['success' => false]);
        }

        return;
    }
}
