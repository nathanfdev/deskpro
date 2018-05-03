<?php

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Form\Model\NewTopic;
use Application\AgentBundle\Form\Type\NewTopic as NewTopicType;
use Application\AgentBundle\Validator\NewTopicValidator;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Entity\SearchStickyResult;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\TopicComment;
use Application\DeskPRO\Entity\TopicRevision;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use DateTime;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GuideController.
 *
 * @Feature("guides")
 */
class GuideController extends PublishController
{
    public function viewAction($topic_id)
    {
        /** @var Topic $topic */
        $topic = $this->em->find(Topic::class, $topic_id);

        if (!$topic) {
            throw $this->createNotFoundException();
        }

        $comments = $this->em->getRepository(TopicComment::class)->getComments($topic);

        $relatedFinder  = new RelatedContentFinder($this->person, $topic);
        $relatedContent = $relatedFinder->getRelatedEntities(true);

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.edittopic', $this->person->id);

        $stickySearchWords = $this->em->getRepository(SearchStickyResult::class)->getWordsForObject($topic);
        $ratedSearches     = $this->em->getRepository(SearchLog::class)->getRatedSearchesFor('topic', $topic['id'], 'counted');

        if ($topic->getGuide() && $topic->getGuide()->getBrand()) {
            $brand = $topic->getGuide()->getBrand();
        } else {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
            $brand   = $this->em->getRepository(Brand::class)->find($brandId);
        }
        $guides = $this->em->getRepository(Guide::class)->findBy(['brand' => $brand]);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        $perms = [
            'can_edit'   => $this->person->PermissionsManager->PublishChecker->canEdit($topic),
            'can_delete' => $this->person->PermissionsManager->PublishChecker->canDelete($topic),
        ];

        return $this->render('AgentBundle:Guide:view.html.twig', [
            'topic'               => $topic,
            'comments'            => $comments,
            'guides'              => $guides,
            'related_content'     => $relatedContent,
            'state'               => $state,
            'sticky_search_words' => $stickySearchWords,
            'rated_searches'      => $ratedSearches,
            'perms'               => $perms,
            'brands'              => $brands,
        ]);
    }

    public function viewRevisionsAction($topic_id)
    {
        $topic = $this->em->find(Topic::class, $topic_id);

        return $this->render('AgentBundle:Guide:view-revisions-tab.html.twig', [
            'topic' => $topic,
        ]);
    }

    public function ajaxSaveCommentAction($topic_id)
    {
        $topic = $this->em->find(Topic::class, $topic_id);

        if (!$topic || !$this->in->getString('content')) {
            throw $this->createNotFoundException();
        }

        $comment = new TopicComment();
        $comment->setTopic($topic);
        $comment->setPerson($this->person);
        $comment->setContent($this->in->getString('content'));
        $comment->setStatus('visible');
        $comment->setDateCreated(new DateTime());

        if ($this->person->hasPerm('agent_publish.validate')) {
            $comment->setIsReviewed(true);
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $this->render('AgentBundle:Guide:view-comment.html.twig', [
            'comment' => $comment,
        ]);
    }

    public function ajaxSaveAction($topic_id)
    {
        $topic = $this->em->find(Topic::class, $topic_id);
        $rev   = null;

        if (!$topic) {
            throw $this->createNotFoundException();
        }

        $action = $this->in->getString('action');

        if ($action == 'delete') {
            if (!$this->person->PermissionsManager->PublishChecker->canDelete($topic)) {
                return $this->createJsonResponse(['success' => false]);
            }
        } else {
            if (!$this->person->PermissionsManager->PublishChecker->canEdit($topic)) {
                return $this->createJsonResponse(['success' => false]);
            }
        }

        $data = ['success' => 1];

        $this->em->beginTransaction();

        switch ($action) {
            case 'status':
                $topic->setStatusCode($this->in->getString('status'));
                if ($topic['status_code'] == 'published' && !$this->person->hasPerm('agent_publish.validate')) {
                    $topic['status_code'] = 'hidden.unpublished';
                }
                break;

            case 'title':
                $topic->setTitle($this->in->getString('title'));
                /** @var TopicRevision $rev */
                $rev = ContentRevisionUtil::findOrCreate($topic, 'title', $this->person);
                $rev->setTitle($topic->getTitle());
                break;

            case 'slug':
                $topic->setSlug(Strings::slugifyTitle($this->in->getString('slug')) ?: 'view');
                $data['slug'] = $topic['slug'];
                break;

            case 'add-related':
                $updater = new RelatedContentUpdate($topic);
                $updater->addRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-related':
                $updater = new RelatedContentUpdate($topic);
                $updater->removeRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'content':

                $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.edittopic', $this->person->id);

                $topic->setContent($this->person->hasPerm('agent_publish.can_insert_html')
                    ? $this->in->getCleanValue('content', 'string', null, ['noclean' => true])
                    : $this->in->getCleanValue('content', 'html'));

                $topic->setContentInput($this->in->getStringRaw('content_input'));
                $topic->setContentInputType($this->in->getString('content_input_type'));

                $data['content_html'] = $this->renderView('AgentBundle:Guide:view-content-tab.html.twig', [
                    'topic' => $topic,
                ]);

                $data['content_input'] = $topic->getContentInput();

                /** @var TopicRevision $rev */
                $rev = ContentRevisionUtil::findOrCreate($topic, 'content', $this->person);
                $rev->setContent($topic->getContent());

                break;

            case 'guide':
                $guide = $this->em->find(Guide::class, $this->in->getUInt('guide_id'));
                $this->moveTopic($topic, $guide);
                $data['guide_id'] = $guide->getId();
                $topic->setParent(null);
                $data['parent_id'] = 0;
                break;

            case 'delete':
                $topic->status_code = 'hidden.deleted';
                break;

            case 'undelete':
                $topic->status_code = 'published';
                break;

            case 'auto-unpub':
                $date   = date_create('@'.$this->in->getUInt('end_timestamp'));
                $action = $this->in->getString('end_action');

                $topic->date_end   = $date;
                $topic->end_action = $action;
                break;

            case 'remove-auto-unpub':
                $topic->date_end   = null;
                $topic->end_action = null;
                break;

            case 'auto-pub':
                $date = date_create('@'.$this->in->getUInt('pub_timestamp'));

                $topic->setDatePublished($date);
                break;

            case 'remove-auto-pub':
                $topic->setDatePublished(null);
                break;

            case 'no_content':
                $topic->setNoContent($this->in->getBoolInt('no_content'));
                break;
        }

        $this->em->persist($topic);

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
     * @param Topic $topic
     * @param Guide $guide
     */
    protected function moveTopic($topic, $guide)
    {
        $topic->setGuide($guide);
        $children = $topic->getChildren();
        if (count($children)) {
            foreach ($children as $child) {
                $this->moveTopic($child, $guide);
                $this->em->persist($child);
            }
        }
    }

    public function ajaxGetGuidesByBrandAction($brand_id)
    {
        $guides = $this->em->getRepository(Guide::class)->findBy(['brand' => $brand_id]);

        $guide = $guides[0];

        return $this->render('AgentBundle:Common:select-standard.html.twig', [
            'name'             => 'newtopic[guide_id]',
            'id'               => 'new_topic_guide_id',
            'add_classname'    => 'guide_id',
            'add_attr'         => '',
            'categories'       => $guides,
            'allow_parent_sel' => true,
            'selected'         => $guide->getId(),
        ]);
    }

    public function ajaxGetTopicsByGuideAction($guide_id)
    {
        $topics = $this->em->getRepository(Topic::class)->getInHierarchy(false, $guide_id);

        array_unshift($topics, ['id' => 0, 'title' => '-', 'parent_id' => 0]);

        return $this->render('AgentBundle:Common:select-standard.html.twig', [
            'name'             => 'newtopic[parent_id]',
            'id'               => '_parent',
            'add_classname'    => 'parent_id',
            'add_attr'         => '',
            'categories'       => $topics,
            'allow_parent_sel' => true,
            'selected'         => 0,
        ]);
    }

    public function newTopicAction()
    {
        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.new_topic', $this->person->id);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        $guides = $this->em->getRepository(Guide::class)->findBy(['brand' => $brands[0]->getId()]);

        $topics = [];
        if (count($guides) > 0) {
            $topics = $this->em->getRepository(Topic::class)->getInHierarchy(false, $guides[0]);
        }

        array_unshift($topics, ['id' => 0, 'title' => '-', 'parent_id' => 0]);

        return $this->render('AgentBundle:Guide:new-topic.html.twig', [
            'guides' => $guides,
            'state'  => $state,
            'brands' => $brands,
            'topics' => $topics,
        ]);
    }

    public function newTopicSaveAction(Request $request)
    {
        $newTopic = new NewTopic($this->person);

        $formType = new NewTopicType();
        $form     = $this->get('form.factory')->create($formType, $newTopic);

        $this->db->executeUpdate("DELETE FROM people_prefs WHERE name = 'agent.ui.state.new_topic' AND person_id = ?", [$this->person->id]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $form->isValid();

            $validator = new NewTopicValidator();
            if (!$validator->isValid($newTopic)) {
                return $this->createJsonResponse([
                    'error'       => true,
                    'error_codes' => $validator->getErrorGroups(),
                ]);
            }
            $newTopic->save();

            $topic = $newTopic->getTopic();

            $rev = ContentRevisionUtil::findOrCreate($topic, ['title', 'content'], $this->person);
            if ($rev) {
                $this->em->persist($rev);
                $this->em->flush();
            }

            $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.new_topic', $this->person->id);

            return $this->createJsonResponse([
                'success'  => true,
                'topic_id' => $topic['id'],
            ]);
        } else {
            return $this->createJsonResponse([
                'success' => false,
            ]);
        }
    }

    public function listAction($guide_id)
    {
        $guide = null;
        if ($guide_id) {
            /** @var Guide $guide */
            $guide = $this->em->getRepository(Guide::class)->find($guide_id);
        }

        if (!$guide) {
            throw $this->createNotFoundException();
        }

        $results = $guide->getTopics();

        $totalResults = count($results);

        $tpl = 'AgentBundle:Guide:filter.html.twig';

        $displayFields = $this->person->getPref('agent.ui.topic-filter-display-fields.0');
        if (!$displayFields) {
            $displayFields = [];
        }

        $guideUserGroups    = [];
        $guideStructureData = [];
        if ($guide) {
            $guideUserGroups = $this->db->fetchAllCol('
                SELECT usergroup_id
                FROM guide2usergroup
                WHERE guide_id = ?
            ', [$guide->getId()]);

            $guideStructureData = $this->getFilteredCategory($guide->getBrand()->getId());
        }

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render($tpl, [
            'results'            => $results,
            'display_fields'     => $displayFields,
            'guide'              => $guide,
            'cat_usergroups'     => $guideUserGroups,
            'cat_structure_data' => $guideStructureData,
            'total_results'      => $totalResults,
            'num_pages'          => 1,
            'cur_page'           => 1,
            'showing_to'         => $totalResults,
            'brands'             => $brands,
        ]);
    }

    public function addCategoryFormAction($type = '')
    {
        $brandId = $this->in->getUInt('brand_id');

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:Publish:new-guide.html.twig', [
            'type'           => 'guide',
            'all_categories' => [],
            'brands'         => $brands,
            'brand_id'       => $brandId,
        ]);
    }

    /**
     * @param int $brandId
     *
     * @return array
     */
    private function getFilteredCategory($brandId)
    {
        /** @var Guide[] $unFilteredGuides */
        $unFilteredGuides = $this->em->getRepository(Guide::class)->findAll();

        $guides = [];

        foreach ($unFilteredGuides as $c) {
            if ($brandId == $c->getBrand()->getId()) {
                $guides[] = [
                    'id'       => $c->getId(),
                    'label'    => $c->getTitle(),
                    'brand_id' => $c->getBrand()->getId(),
                    'children' => [],
                ];
            }
        }

        return $guides;
    }

    //###########################################################################
    // Compare revisions
    //###########################################################################

    public function compareRevisionsAction($rev_old_id, $rev_new_id)
    {
        $diffInfo = ContentRevisionUtil::compareRevisions(TopicRevision::class, $rev_old_id, $rev_new_id);

        return $this->render('AgentBundle:Guide:compare-revs.html.twig', [
            'rendered_content_diff' => $diffInfo['rendered_content_diff'],
            'rendered_title_diff'   => $diffInfo['rendered_title_diff'],
        ]);
    }
}
