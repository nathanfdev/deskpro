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

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Form\Model\NewTopic;
use Application\AgentBundle\Form\Type\NewTopic as NewTopicType;
use Application\AgentBundle\Validator\NewTopicValidator;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Manual;
use Application\DeskPRO\Entity\ManualTopic;
use Application\DeskPRO\Entity\ManualTopicComment;
use Application\DeskPRO\Entity\ManualTopicRevision;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Entity\SearchStickyResult;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use DateTime;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;

class ManualController extends PublishController
{
    public function viewAction($manual_topic_id)
    {
        /** @var ManualTopic $manualTopic */
        $manualTopic = $this->em->find(ManualTopic::class, $manual_topic_id);

        if (!$manualTopic) {
            throw $this->createNotFoundException();
        }

        $comments = $this->em->getRepository(ManualTopicComment::class)->getComments($manualTopic);

        $relatedFinder  = new RelatedContentFinder($this->person, $manualTopic);
        $relatedContent = $relatedFinder->getRelatedEntities(true);

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.editmanualtopic', $this->person->id);

        $stickySearchWords = $this->em->getRepository(SearchStickyResult::class)->getWordsForObject($manualTopic);
        $ratedSearches     = $this->em->getRepository(SearchLog::class)->getRatedSearchesFor('manualtopic', $manualTopic['id'], 'counted');

        if ($manualTopic->getManual() && $manualTopic->getManual()->getBrand()) {
            $brand = $manualTopic->getManual()->getBrand();
        } else {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
            $brand   = $this->em->getRepository(Brand::class)->find($brandId);
        }
        $manuals = $this->em->getRepository(Manual::class)->findBy(['brand' => $brand]);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        $perms = [
            'can_edit'   => $this->person->PermissionsManager->PublishChecker->canEdit($manualTopic),
            'can_delete' => $this->person->PermissionsManager->PublishChecker->canDelete($manualTopic),
        ];

        return $this->render('AgentBundle:Manual:view.html.twig', [
            'manual_topic'        => $manualTopic,
            'comments'            => $comments,
            'manuals'             => $manuals,
            'related_content'     => $relatedContent,
            'state'               => $state,
            'sticky_search_words' => $stickySearchWords,
            'rated_searches'      => $ratedSearches,
            'perms'               => $perms,
            'brands'              => $brands,
        ]);
    }

    public function viewRevisionsAction($manual_topic_id)
    {
        $manualTopic = $this->em->find(ManualTopic::class, $manual_topic_id);

        return $this->render('AgentBundle:Manual:view-revisions-tab.html.twig', [
            'manual_topic' => $manualTopic,
        ]);
    }

    public function ajaxSaveCommentAction($manual_topic_id)
    {
        $manualTopic = $this->em->find(ManualTopic::class, $manual_topic_id);

        if (!$manualTopic || !$this->in->getString('content')) {
            throw $this->createNotFoundException();
        }

        $comment = new ManualTopicComment();
        $comment->setManualTopic($manualTopic);
        $comment->setPerson($this->person);
        $comment->setContent($this->in->getString('content'));
        $comment->setStatus('visible');
        $comment->setDateCreated(new DateTime());

        if ($this->person->hasPerm('agent_publish.validate')) {
            $comment->setIsReviewed(true);
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $this->render('AgentBundle:Manual:view-comment.html.twig', [
            'comment' => $comment,
        ]);
    }

    public function ajaxSaveAction($manual_topic_id)
    {
        $manualTopic = $this->em->find(ManualTopic::class, $manual_topic_id);
        $rev         = null;

        if (!$manualTopic) {
            throw $this->createNotFoundException();
        }

        $action = $this->in->getString('action');

        if ($action == 'delete') {
            if (!$this->person->PermissionsManager->PublishChecker->canDelete($manualTopic)) {
                return $this->createJsonResponse(['success' => false]);
            }
        } else {
            if (!$this->person->PermissionsManager->PublishChecker->canEdit($manualTopic)) {
                return $this->createJsonResponse(['success' => false]);
            }
        }

        $data = ['success' => 1];

        $this->em->beginTransaction();

        switch ($action) {
            case 'status':
                $manualTopic->setStatusCode($this->in->getString('status'));
                if ($manualTopic['status_code'] == 'published' && !$this->person->hasPerm('agent_publish.validate')) {
                    $manualTopic['status_code'] = 'hidden.unpublished';
                }
                break;

            case 'title':
                $manualTopic->setTitle($this->in->getString('title'));
                /** @var ManualTopicRevision $rev */
                $rev = ContentRevisionUtil::findOrCreate($manualTopic, 'title', $this->person);
                $rev->setTitle($manualTopic->getTitle());
                break;

            case 'slug':
                $manualTopic->setSlug(Strings::slugifyTitle($this->in->getString('slug')) ?: 'view');
                $data['slug'] = $manualTopic['slug'];
                break;

            case 'add-related':
                $updater = new RelatedContentUpdate($manualTopic);
                $updater->addRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-related':
                $updater = new RelatedContentUpdate($manualTopic);
                $updater->removeRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'content':

                $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.editmanualtopic', $this->person->id);

                $manualTopic->setContent($this->person->hasPerm('agent_publish.can_insert_html')
                    ? $this->in->getCleanValue('content', 'string', null, ['noclean' => true])
                    : $this->in->getCleanValue('content', 'html'));

                $data['content_html'] = $this->renderView('AgentBundle:Manual:view-content-tab.html.twig', [
                    'manual_topic' => $manualTopic,
                ]);

                /** @var ManualTopicRevision $rev */
                $rev = ContentRevisionUtil::findOrCreate($manualTopic, 'content', $this->person);
                $rev->setContent($manualTopic->getContent());

                break;

            case 'manual':
                $manual = $this->em->find(Manual::class, $this->in->getUInt('category_id'));
                $manualTopic->setManual($manual);
                $data['manual_id'] = $manual->getId();
                break;

            case 'delete':
                $manualTopic->status_code = 'hidden.deleted';
                break;

            case 'undelete':
                $manualTopic->status_code = 'published';
                break;

            case 'auto-unpub':
                $date   = date_create('@'.$this->in->getUInt('end_timestamp'));
                $action = $this->in->getString('end_action');

                $manualTopic->date_end   = $date;
                $manualTopic->end_action = $action;
                break;

            case 'remove-auto-unpub':
                $manualTopic->date_end   = null;
                $manualTopic->end_action = null;
                break;

            case 'auto-pub':
                $date = date_create('@'.$this->in->getUInt('pub_timestamp'));

                $manualTopic->setDatePublished($date);
                break;

            case 'remove-auto-pub':
                $manualTopic->setDatePublished(null);
                break;
        }

        $this->em->persist($manualTopic);

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

    public function newTopicAction()
    {
        $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');

        $manuals = $this->em->getRepository(Manual::class)->findAll();

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.new_topic', $this->person->id);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:Manual:new-topic.html.twig', [
            'manuals' => $manuals,
            'state'   => $state,
            'brands'  => $brands,
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
                'success'         => true,
                'manual_topic_id' => $topic['id'],
            ]);
        } else {
            return $this->createJsonResponse([
                'success' => false,
            ]);
        }
    }

    public function listAction($manual_id)
    {
        $manual = null;
        if ($manual_id) {
            /** @var Manual $manual */
            $manual = $this->em->getRepository(Manual::class)->find($manual_id);
        }

        if (!$manual) {
            throw $this->createNotFoundException();
        }

        $results = $manual->getTopics();

        $totalResults = count($results);

        $tpl = 'AgentBundle:Manual:filter.html.twig';

        $manualUserGroups = [];
        if ($manual) {
            $manualUserGroups = $this->db->fetchAllCol('
                SELECT usergroup_id
                FROM manual2usergroup
                WHERE manual_id = ?
            ', [$manual->getId()]);
        }

        return $this->render($tpl, [
            'results'        => $results,
            'manual'         => $manual,
            'cat_usergroups' => $manualUserGroups,
            'total_results'  => $totalResults,
            'num_pages'      => 1,
            'cur_page'       => 1,
            'showing_to'     => $totalResults,
        ]);
    }

    public function addCategoryFormAction($type = '')
    {
        $brandId = $this->in->getUInt('brand_id');

        /** @var Brand[] $brands */
        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:Publish:new-manual.html.twig', [
            'type'           => 'manual',
            'all_categories' => [],
            'brands'         => $brands,
            'brand_id'       => $brandId,
        ]);
    }
}
