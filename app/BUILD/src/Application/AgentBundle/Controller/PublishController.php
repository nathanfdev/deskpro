<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\People\PermissionUtil;
use Application\DeskPRO\Publish\AgentHelper as PublishHelper;
use Application\DeskPRO\Publish\CategoryEdit as PublishCategoryEdit;
use Application\DeskPRO\Searcher\ArticleSearch;
use Application\DeskPRO\Searcher\DownloadSearch;
use Application\DeskPRO\Searcher\FeedbackSearch;
use Application\DeskPRO\Searcher\NewsSearch;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;

class PublishController extends AbstractController
{
    /**
     * @var \Application\DeskPRO\Publish\AgentHelper
     */
    protected $publish_helper;

    public function requireRequestToken($action, $arguments = null)
    {
        if ($action == 'whoViewedAction') {
            return false;
        }

        return true;
    }

    protected function init()
    {
        parent::init();

        $this->publish_helper = new PublishHelper();
        $this->publish_helper->setPersonContext($this->person);

        if ($this->in->getString('specific_type')) {
            $this->publish_helper->setEnabledTypes([$this->in->getString('specific_type')]);
        }
    }

    public function getSectionDataAction()
    {
        $data = [];

        #------------------------------
        # KB
        #------------------------------

        $kb_cats        = $this->publish_helper->getCategoryStructure(PublishHelper::ARTICLES);
        $kb_repo        = $this->em->getRepository('DeskPRO:ArticleCategory');
        $kb_cats_counts = $this->publish_helper->getCategoryCounts(PublishHelper::ARTICLES);

        $kb_translate_queue = [0 => 0];

        $langs = $this->container->getLanguageData()->getAll();
        foreach ($langs as $lang) {
            $c = $this->db->fetchColumn("
                SELECT COUNT(*) FROM articles
                LEFT JOIN object_lang ON (object_lang.ref_type = 'articles' AND object_lang.ref_id = articles.id AND object_lang.language_id = ?)
                WHERE
                    articles.status = 'published'
                    AND (articles.language_id IS NULL OR articles.language_id != ?)
                    AND object_lang.id IS NULL
            ", [$lang->getId(), $lang->getId()]);

            $kb_translate_queue[$lang->getId()] = $c;
            $kb_translate_queue[0] += $c;
        }

        #------------------------------
        # News
        #------------------------------

        $news_cats        = $this->publish_helper->getCategoryStructure(PublishHelper::NEWS);
        $news_repo        = $this->em->getRepository('DeskPRO:NewsCategory');
        $news_cats_counts = $this->publish_helper->getCategoryCounts(PublishHelper::NEWS);

        #------------------------------
        # Downloads
        #------------------------------

        $download_cats        = $this->publish_helper->getCategoryStructure(PublishHelper::DOWNLOADS);
        $download_repo        = $this->em->getRepository('DeskPRO:DownloadCategory');
        $download_cats_counts = $this->publish_helper->getCategoryCounts(PublishHelper::DOWNLOADS);

        #------------------------------
        # Glossary
        #------------------------------

        $glossary_words = $this->publish_helper->getGlossaryWordsIndex();
        $glossary_count = Arrays::countMulti($glossary_words);

        #------------------------------
        # Comments and counts
        #------------------------------

        $counts                        = [];
        $counts['validating_comments'] = $this->publish_helper->getValidatingCommentsCount();
        $counts['drafts']              = $this->publish_helper->getDraftsCount();
        $counts['all_drafts']          = $this->publish_helper->getDraftsCount(false);
        $counts['pending']             = $this->db->fetchColumn('SELECT COUNT(*) FROM article_pending_create');

        $usergroups = $this->container->getDataService('Usergroup')->getUserUsergroups();

        $counts['comments'] = $this->publish_helper->getCommentsCountInfo();

        $data['section_html'] = $this->renderView('AgentBundle:Publish:window-section.html.twig', [
            'usergroups' => $usergroups,
            'counts'     => $counts,

            'kb_cats'            => $kb_cats,
            'kb_repo'            => $kb_repo,
            'kb_cats_counts'     => $kb_cats_counts,
            'kb_translate_queue' => $kb_translate_queue,

            'news_cats'        => $news_cats,
            'news_repo'        => $news_repo,
            'news_cats_counts' => $news_cats_counts,

            'download_cats'        => $download_cats,
            'download_repo'        => $download_repo,
            'download_cats_counts' => $download_cats_counts,

            'glossary_words' => $glossary_words,
            'glossary_count' => $glossary_count,
        ]);

        return $this->createJsonResponse($data);
    }

    ############################################################################
    # comments
    ############################################################################

    public function listValidatingCommentsAction()
    {
        $perPage = 25;

        $currentPage = $this->in->getUint('page');
        if (!$currentPage) {
            $currentPage = 1;
        }

        $limit = [
            'max'    => $perPage,
            'offset' => ($currentPage - 1) * $perPage,
        ];

        $pageinfo = null;
        $total    = null;
        if (!@$_REQUEST['_partial']) {
            $total    = $this->publish_helper->getValidatingCommentsCount();
            $pageinfo = Numbers::getPaginationPages($total, $currentPage, $perPage);
        }

        $validating_comments = $this->publish_helper->getValidatingComments($limit);

        $tpl = 'AgentBundle:Publish:validating-comments.html.twig';
        if (@$_REQUEST['_partial']) {
            $tpl = 'AgentBundle:Publish:validating-comments-page.html.twig';
        }

        return $this->render($tpl, [
            'single_type'         => $this->publish_helper->getSingleSpecificType(),
            'validating_comments' => $validating_comments,
            'total'               => $total,
            'pageinfo'            => $pageinfo,
        ]);
    }

    public function approveCommentAction($typename, $comment_id)
    {
        if (!$this->person->hasPerm('agent_publish.validate')) {
            throw $this->createNotFoundException();
        }

        $entity = $this->_getCommentEntityName($typename);

        /** @var CommentAbstract $comment */
        $comment = $this->em->find($entity, $comment_id);
        /** @var ContentAbstract $object */
        $object    = $comment->getObject();
        $oldStatus = $comment->getStatus();
        $comment->setStatus(CommentAbstract::STATUS_VISIBLE);
        if ($oldStatus !== CommentAbstract::STATUS_VISIBLE) {
            $object->addComment($comment);
        }
        $this->em->persist($comment);
        $this->em->persist($object);
        $this->em->flush([$comment, $object]);

        $this->_sendCommentApprovedNotification($comment);

        return $this->createJsonResponse([
            'comment_id' => $comment['id'],
            'typename'   => $typename,
        ]);
    }

    public function deleteCommentAction($typename, $comment_id)
    {
        if (!$this->person->hasPerm('agent_publish.validate')) {
            throw $this->createNotFoundException();
        }

        $entity = $this->_getCommentEntityName($typename);
        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $comment = $this->em->find($entity, $comment_id);
        if (!$comment) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($comment);
        $this->em->flush();

        $this->_sendCommentDeletedNotification($comment);

        return $this->createJsonResponse([
            'comment_id' => $comment['id'],
            'typename'   => $typename,
        ]);
    }

    public function validatingCommentsMassActionsAction($action)
    {
        if (!$this->person->hasPerm('agent_publish.validate')) {
            throw $this->createNotFoundException();
        }

        $data = $this->in->getCleanValueArray('content', 'array', 'string');

        $this->em->beginTransaction();

        foreach ($data as $typename => $ids) {
            $entity = $this->_getCommentEntityName($typename);
            if (!$entity) {
                continue;
            }

            /** @var CommentAbstract[] $comments */
            $comments = $this->em->getRepository($entity)->getByIds($ids);
            foreach ($comments as $comment) {
                if ($action == 'approve') {
                    $comment->setStatus(CommentAbstract::STATUS_VISIBLE);

                    /** @var ContentAbstract $object */
                    $object = $comment->getObject();
                    //TODO should be using object getter but it doesn't work for obscure reasons
                    $object->setNumComments($object->num_comments + 1);

                    $this->em->persist($comment);
                    $this->em->persist($object);
                    $this->_sendCommentApprovedNotification($comment);
                } else {
                    $this->em->remove($comment);
                    $this->_sendCommentDeletedNotification($comment);
                }
            }
        }

        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    public function commentInfoAction($typename, $comment_id)
    {
        $entity = $this->_getCommentEntityName($typename);

        $comment = $this->em->find($entity, $comment_id);

        if (!$comment) {
            throw $this->createNotFoundException();
        }

        return $this->createJsonResponse([
            'comment_id'   => $comment['id'],
            'content_type' => $typename,
            'comment_text' => $comment->content,
        ]);
    }

    public function saveCommentAction($typename, $comment_id)
    {
        $entity = $this->_getCommentEntityName($typename);

        $comment          = $this->em->find($entity, $comment_id);
        $comment->content = $this->in->getString('comment');

        if (!$comment) {
            throw $this->createNotFoundException();
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $this->createJsonResponse([
            'comment_id'   => $comment['id'],
            'content_type' => $typename,
            'comment_html' => $comment->getContentHtml(),
        ]);
    }

    public function getNewTicketCommentInfoAction($typename, $comment_id)
    {
        $entity = $this->_getCommentEntityName($typename);

        $comment = $this->em->find($entity, $comment_id);

        switch ($typename) {
            case 'articles':
                $objectUrl = $this->get('router')->generate('agent_kb_article', ['article_id' => $comment->getObject()->getId()]);
                break;
            case 'downloads':
                $objectUrl = $this->get('router')->generate('agent_downloads_view', ['download_id' => $comment->getObject()->getId()]);
                break;
            case 'news':
                $objectUrl = $this->get('router')->generate('agent_news_view', ['news_id' => $comment->getObject()->getId()]);
                break;
            case 'feedback':
                $objectUrl = $this->get('router')->generate('agent_feedback_view', ['feedback_id' => $comment->getObject()->getId()]);
                break;
        }

        return $this->createJsonResponse([
            'message'      => $comment->getContentPlain(),
            'status'       => $comment->status,
            'content_type' => $typename,
            'comment_id'   => $comment_id,
            'name'         => $comment->getPerson()->display_name,
            'person_id'    => $comment->getPersonId(),
            'email'        => $comment->getUserEmail(),
            'object_title' => $comment->getObject()->getTitle(),
            'object_url'   => $objectUrl,
        ]);
    }

    protected function _getCommentEntityName($typename)
    {
        switch ($typename) {
            case 'articles':
                return 'DeskPRO:ArticleComment';
            case 'downloads':
                return 'DeskPRO:DownloadComment';
            case 'news':
                return 'DeskPRO:NewsComment';
            case 'feedback':
                return 'DeskPRO:FeedbackComment';
        }
    }

    protected function _sendCommentApprovedNotification($comment)
    {
        if ($comment->getUserEmail()) {
            $message = $this->container->getMailer()->createMessage();
            if ($comment->person) {
                $message->setTo($comment->person->getPrimaryEmailAddress(), $comment->person->getDisplayName());
            } else {
                $message->setTo($comment->getUserEmail());
            }
            $message->setTemplate('DeskPRO:emails_user:comment-approved.html.twig', [
                'comment' => $comment,
            ]);
            $this->container->getMailer()->send($message);
        }
    }

    public function _sendCommentDeletedNotification($comment)
    {
        if ($comment->getUserEmail()) {
            $message = $this->container->getMailer()->createMessage();
            if ($comment->person) {
                $message->setTo($comment->person->getPrimaryEmailAddress(), $comment->person->getDisplayName());
            } else {
                $message->setTo($comment->getUserEmail());
            }
            $message->setTemplate('DeskPRO:emails_user:comment-deleted.html.twig', [
                'comment' => $comment,
            ]);
            $this->container->getMailer()->send($message);
        }
    }

    ############################################################################
    # list comments
    ############################################################################

    public function listCommentsAction($type)
    {
        if ($type !== 'all') {
            try {
                $this->publish_helper->getCommentTypeInfo($type);
            } catch (\Exception $e) {
                throw $this->createNotFoundException();
            }

            $this->publish_helper->setEnabledTypes([$type]);
        }

        $perPage = 25;

        $currentPage = $this->in->getUint('page');
        if (!$currentPage) {
            $currentPage = 1;
        }

        $limit = [
            'max'    => $perPage,
            'offset' => ($currentPage - 1) * $perPage,
        ];

        $pageinfo = null;
        $total    = null;
        if (!@$_REQUEST['_partial']) {
            $counts = $this->publish_helper->getCommentsCountInfo();
            $total  = $counts[$type];

            $pageinfo = Numbers::getPaginationPages($total, $currentPage, $perPage);
        }

        $comments = $this->publish_helper->getComments($limit);

        $tpl = 'AgentBundle:Publish:list-comments.html.twig';
        if (@$_REQUEST['_partial']) {
            $tpl = 'AgentBundle:Publish:list-comments-page.html.twig';
        }

        return $this->render($tpl, [
            'type'     => $type,
            'comments' => $comments,
            'total'    => $total,
            'pageinfo' => $pageinfo,
        ]);
    }

    public function listValidatingFeedbackCommentsAction()
    {
        $this->publish_helper->setEnabledTypes(['feedback']);

        return $this->listValidatingCommentsAction();
    }

    ############################################################################
    # content validating
    ############################################################################

    public function listDraftsAction($type)
    {
        if ($type == 'all') {
            return $this->listDrafts(true);
        } else {
            return $this->listDrafts(false);
        }
    }

    protected function listDrafts($get_all)
    {
        $perPage = 25;

        $currentPage = $this->in->getUint('page');
        if (!$currentPage) {
            $currentPage = 1;
        }

        $pageinfo = null;
        $total    = null;
        if (!@$_REQUEST['_partial']) {
            $total    = $this->publish_helper->getDraftsCount();
            $pageinfo = Numbers::getPaginationPages($total, $currentPage, $perPage);
        }

        $drafts = $this->publish_helper->getDraftContent(null, 'ASC', $get_all);

        $tpl = 'AgentBundle:Publish:drafts.html.twig';
        if (@$_REQUEST['_partial']) {
            $tpl = 'AgentBundle:Publish:drafts-page.html.twig';
        }

        return $this->render($tpl, [
            'drafts'   => $drafts,
            'total'    => $total,
            'pageinfo' => $pageinfo,
            'all'      => $get_all,
        ]);
    }

    public function draftsMassActionsAction($action)
    {
        $data = $this->in->getCleanValueArray('content', 'array', 'string');

        $this->em->beginTransaction();

        $affected_content = [];

        foreach ($data as $type => $ids) {
            $entity = $this->publish_helper->getEntityNameFor($type);
            if (!$entity) {
                continue;
            }

            $results = $this->em->getRepository($entity)->getByIds($ids);
            foreach ($results as $r) {
                if ($r['status_code'] != 'hidden.draft' || $r->person['id'] != $this->person['id'] && !$this->person['can_admin']) {
                    continue;
                }
                if ($action == 'delete') {
                    $this->em->remove($r);
                    $affected_content[] = ['typename' => $type, 'contentId' => $r->id];
                } elseif ($action == 'publish') {
                    $r->setStatusCode('published');
                    $affected_content[] = ['typename' => $type, 'contentId' => $r->id];
                }

                if ($r instanceof Article && !count($r->categories)) {
                    $cat = $this->em->createQuery('
                        SELECT c
                        FROM DeskPRO:ArticleCategory c
                        ORDER BY c.id ASC
                    ')->setMaxResults(1)->getOneOrNullResult();
                    if ($cat) {
                        $r->addToCategory($cat);
                    }
                }
            }
        }

        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse([
            'success'  => true,
            'affected' => $affected_content,
        ]);
    }

    ############################################################################
    # saving sticky words
    ############################################################################

    public function saveStickySearchWordsAction($type, $content_id)
    {
        $entity_name = null;
        switch ($type) {
            case 'articles':
                $entity_name = 'DeskPRO:Article';
                break;
            case 'article':
                $entity_name = 'DeskPRO:Article';
                break;
            case 'downloads':
                $entity_name = 'DeskPRO:Download';
                break;
            case 'download':
                $entity_name = 'DeskPRO:Download';
                break;
            case 'news':
                $entity_name = 'DeskPRO:News';
                break;
            case 'feedback':
                $entity_name = 'DeskPRO:Feedback';
                break;
        }

        $this->db->beginTransaction();

        // Lets just recreate them all
        $this->db->executeUpdate('
            DELETE FROM search_sticky_result
            WHERE object_type = ? AND object_id = ?
        ', [$entity_name, $content_id]);

        foreach ($this->in->getCleanValueArray('words', 'string', 'discard') as $word) {
            $word = Strings::utf8_strtolower($word);
            $word = Strings::utf8_accents_to_ascii($word);

            if (!$word || !$entity_name || !$content_id) {
                continue;
            }

            $this->db->replace('search_sticky_result', [
                'word'        => $word,
                'object_type' => $entity_name,
                'object_id'   => $content_id,
            ]);
        }

        $this->db->commit();

        return $this->createJsonResponse([
            'success' => 1,
        ]);
    }

    ############################################################################
    # ratings
    ############################################################################

    public function ratingWhoVotedAction($object_type, $object_id)
    {
        $ratings = $this->em->createQuery('
            SELECT r
            FROM DeskPRO:Rating r
            LEFT JOIN r.person p
            WHERE r.object_type = ?1 AND r.object_id = ?2
            ORDER BY r.id DESC
        ')->execute([1 => $object_type, 2 => $object_id]);

        return $this->render('AgentBundle:Publish:rating-who-voted.html.twig', [
            'ratings'     => $ratings,
            'object_type' => $object_type,
        ]);
    }

    ############################################################################
    # saving categories
    ############################################################################

    public function saveCategoriesAction($type)
    {
        #------------------------------
        # Figure out which table
        #------------------------------

        $entity_name = null;
        switch ($type) {
            case 'article':
                $entity_name = 'DeskPRO:ArticleCategory';
                break;
            case 'download':
                $entity_name = 'DeskPRO:DownloadCategory';
                break;
            case 'news':
                $entity_name = 'DeskPRO:NewsCategory';
                break;
        }

        if (!$entity_name) {
            return $this->createJsonResponse(['Invalid type']);
        }

        $repos      = $this->em->getRepository($entity_name);
        $table      = $repos->getTableName();
        $perm_table = $repos->getPermissionTableName();

        #------------------------------
        # Read input
        #------------------------------

        $save_category = [
            'id'         => $this->in->getUint('category.id'),
            'title'      => $this->in->getString('category.title'),
            'usergroups' => $this->in->getCleanValueArray('category.usergroups', 'uint', 'discard'),
        ];

        $save_structure = $this->in->getRaw('category_structure');
        if ($save_structure) {
            $save_structure = @json_decode($save_structure, true);
        }
        if (!$save_structure) {
            $save_structure = [];
        }

        #------------------------------
        # Save category
        #------------------------------

        if ($save_category['id'] && $cat = $this->em->getRepository($entity_name)->find($save_category['id'])) {
            if ($save_category['title']) {
                $cat->title = $save_category['title'];
                $this->db->update($table, ['title' => $cat->title], ['id' => $cat->id]);
            }

            $this->db->delete($perm_table, ['category_id' => $cat->id]);

            // Everyone implies all groups
            if (in_array(1, $save_category['usergroups'])) {
                $this->db->replace($perm_table, ['category_id' => $cat->id, 'usergroup_id' => 1]);
            } else {
                $usergroups = $this->container->getDataService('Usergroup')->getUserUsergroups();
                foreach ($save_category['usergroups'] as $ug_id) {
                    if (!isset($usergroups[$ug_id])) {
                        continue;
                    }

                    $this->db->replace($perm_table, ['category_id' => $cat->id, 'usergroup_id' => $ug_id]);
                }
            }
        }

        #------------------------------
        # Save structure
        #------------------------------

        if ($save_structure) {
            $parent_map         = [];
            $fn_struct_traverse = function ($cats, $parent = 0) use (&$parent_map, &$fn_struct_traverse) {
                foreach ($cats as $cat) {
                    $parent_map[$cat['id']] = $parent;
                    if (!empty($cat['children'])) {
                        $fn_struct_traverse($cat['children'], $cat['id']);
                    }
                }
            };
            $fn_struct_traverse($save_structure);

            $order = 0;
            foreach ($parent_map as $cat_id => $parent_id) {
                $order += 10;
                if ($cat_id == $parent_id) {
                    $parent_id = null;
                }
                if (!$parent_id) {
                    $parent_id = null;
                }

                $this->db->update($table, ['parent_id' => $parent_id, 'display_order' => $order], ['id' => $cat_id]);
            }

            $repos->repair();
        }

        $this->container->getSystemService('publish_structure_cache')->flush();
        PermissionUtil::cleanPermissions();

        return $this->createJsonResponse(['success' => true]);
    }

    public function updateCategoryTitlesAction($type)
    {
        PublishCategoryEdit::updateTitles($type, $this->in->getCleanValueArray('titles', 'string', 'uint'));

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    public function updateCategoryAction($type, $category_id)
    {
        PublishCategoryEdit::update(
            $type,
            $category_id,
            $this->in->getString('title'),
            $this->in->getCleanValueArray('usergroup_ids', 'uint', 'discard')
        );

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    public function updateCategoryOrdersAction($type)
    {
        PublishCategoryEdit::updateOrders($type, $this->in->getCleanValueArray('orders', 'uint', 'discard'));

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    public function updateCategoryStructureAction($type)
    {
        try {
            PublishCategoryEdit::updateStructure(
                $type,
                $this->in->getCleanValueArray('structure', 'uint', 'uint'),
                $this->in->getCleanValueArray('structure_check', 'uint', 'uint')
            );

            PublishCategoryEdit::updateOrders($type, $this->in->getCleanValueArray('orders', 'uint', 'discard'));
        } catch (\OutOfBoundsException $e) {
            return $this->createJsonResponse([
                'error' => true,
            ]);
        }

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    public function addCategoryFormAction($type)
    {
        $entity_name = null;
        switch ($type) {
            case 'article':
                $entity_name = 'DeskPRO:ArticleCategory';
                break;
            case 'download':
                $entity_name = 'DeskPRO:DownloadCategory';
                break;
            case 'news':
                $entity_name = 'DeskPRO:NewsCategory';
                break;
        }

        if (!$entity_name) {
            return $this->createJsonResponse(['Invalid type']);
        }

        $all_categories = $this->em->getRepository($entity_name)->getInHierarchy();

        return $this->render('AgentBundle:Publish:new-cat.html.twig', [
            'type'           => $type,
            'all_categories' => $all_categories,
        ]);
    }

    public function addCategoryFormSaveAction($type)
    {
        $entity_name = null;
        switch ($type) {
            case 'article':
                $entity_name = 'DeskPRO:ArticleCategory';
                break;
            case 'download':
                $entity_name = 'DeskPRO:DownloadCategory';
                break;
            case 'news':
                $entity_name = 'DeskPRO:NewsCategory';
                break;
        }

        if (!$entity_name) {
            return $this->createJsonResponse(['Invalid type']);
        }

        $class      = App::getEntityClass($entity_name);
        $repos      = $this->em->getRepository($entity_name);
        $perm_table = $repos->getPermissionTableName();

        #------------------------------
        # Save
        #------------------------------

        $save_category = [
            'id'         => 0,
            'parent_id'  => $this->in->getUint('category.parent_id'),
            'title'      => $this->in->getString('category.title') ?: 'Untitled',
            'usergroups' => $this->in->getCleanValueArray('category.usergroups', 'uint', 'discard'),
        ];

        $parent_cat = null;
        if ($save_category['parent_id']) {
            $parent_cat = $repos->find($save_category['parent_id']);
        }

        $cat        = new $class();
        $cat->title = $save_category['title'];
        if ($parent_cat) {
            $cat->parent = $parent_cat;
        }
        $this->em->persist($cat);
        $this->em->flush();

        $save_category['id'] = $cat->id;

        // Everyone implies all groups
        if (in_array(1, $save_category['usergroups'])) {
            $this->db->replace($perm_table, ['category_id' => $cat->id, 'usergroup_id' => 1]);
        } else {
            $usergroups = $this->container->getDataService('Usergroup')->getUserUsergroups();
            foreach ($save_category['usergroups'] as $ug_id) {
                if (!isset($usergroups[$ug_id])) {
                    continue;
                }

                $this->db->replace($perm_table, ['category_id' => $cat->id, 'usergroup_id' => $ug_id]);
            }
        }

        $repos->repair();
        $this->container->getSystemService('publish_structure_cache')->flush();
        PermissionUtil::cleanPermissions();

        return $this->createJsonResponse([
            'id' => $cat->id,
        ]);
    }

    public function addCategoryAction($type)
    {
        $cat = PublishCategoryEdit::addCategory($type, $this->in->getString('title'));

        switch ($type) {
            case 'articles':
                $url = $this->generateUrl('agent_kb_list', ['category_id' => $cat->id]);
                break;
            case 'downloads':
                $url = $this->generateUrl('agent_downloads_list', ['category_id' => $cat->id]);
                break;
            case 'news':
                $url = $this->generateUrl('agent_news_list', ['category_id' => $cat->id]);
                break;
            case 'feedback':
                $url = $this->generateUrl('agent_feedback_category', ['category_id' => $cat->id]);
                break;
        }

        return $this->createJsonResponse([
            'success' => true,
            'id'      => $cat['id'],
            'url'     => $url,
        ]);
    }

    public function deleteCategoryAction($type)
    {
        try {
            PublishCategoryEdit::deleteCategory($type, $this->in->getUint('category_id'));
        } catch (\OutOfBoundsException $e) {
            return $this->createJsonResponse([
                'error'       => true,
                'error_code'  => 'not_empty',
                'category_id' => $this->in->getUint('category_id'),
                'type'        => $type,
            ]);
        }

        return $this->createJsonResponse([
            'success'     => true,
            'category_id' => $this->in->getUint('category_id'),
            'type'        => $type,
        ]);
    }

    ############################################################################
    # search
    ############################################################################

    public function searchAction()
    {
        $type = $this->in->getString('content_type');
        switch ($type) {
            case 'articles':
                $searcher = new ArticleSearch();
                $searcher->addTerm('deleted', 'not', 1);
                $helper = 'ArticleResults';
                $cats   = $this->in->getCleanValueArray('article_categories', 'uint', 'discard');
                break;

            case 'news':
                $searcher = new NewsSearch();
                $searcher->addTerm('deleted', 'not', 1);
                $helper = 'NewsResults';
                $cats   = $this->in->getCleanValueArray('news_categories', 'uint', 'discard');
                break;

            case 'downloads':
                $searcher = new DownloadSearch();
                $searcher->addTerm('deleted', 'not', 1);
                $helper = 'DownloadResults';
                $cats   = $this->in->getCleanValueArray('downloads_categories', 'uint', 'discard');
                break;

            case 'feedback':
                $searcher = new FeedbackSearch();
                $searcher->addTerm('deleted', 'not', 1);
                $helper = 'FeedbackResults';
                $cats   = $this->in->getCleanValueArray('feedback_categories', 'uint', 'discard');
                break;

            default:
                throw $this->createNotFoundException();
        }

        $result_cache = false;
        if ($this->in->getUint('cache_id')) {
            $result_cache = $this->em->getRepository('DeskPRO:ResultCache')->find($this->in->getUint('cache_id'));
            if (!$result_cache or $result_cache['person_id'] != $this->person['id']) {
                $result_cache = false;
            }
        }

        $query_type = $this->in->getString('query_type') ?: 'and';
        $query      = $this->in->getString('query');

        if (!$result_cache) {
            $cats = Arrays::removeFalsey($cats);
            if ($cats) {
                $searcher->addTerm('category', 'is', $cats);
            }

            $searcher->addTerm('query', 'is', [
                'query' => $query,
                'type'  => $query_type,
            ]);

            $results = $searcher->getMatches();

            $result_cache                = new ResultCache();
            $result_cache['person']      = $this->person;
            $result_cache['criteria']    = ['terms' => $searcher->getTerms(), 'type' => $type, 'cats' => $cats, 'query' => $query, 'query_type' => $query_type];
            $result_cache['results']     = $results;
            $result_cache['num_results'] = count($results);

            $this->em->persist($result_cache);
            $this->em->flush();
        }

        $page = $this->in->getUint('p');
        if (!$page || $page < 1) {
            $page = 1;
        }
        $perPage = 50;

        $helper = "\\Application\\AgentBundle\\Controller\\Helper\\$helper";
        $helper = $helper::newFromResultCache($this, $result_cache);

        $count = count($result_cache['results']);

        $pageinfo = Numbers::getPaginationPages($count, $page, $perPage);

        $vars = [
            'cache'       => $result_cache,
            'cache_id'    => $result_cache['id'],
            'result_ids'  => $result_cache['results'],
            'num_results' => $count,
            'results'     => $helper->getForPage($page - 1, $perPage),
            'pageinfo'    => $pageinfo,
            'type'        => $result_cache['criteria']['type'],
            'page'        => $page,
            'per_page'    => $perPage,
        ];

        return $this->render('AgentBundle:Publish:search-results-'.$type.'.html.twig', $vars);
    }

    ############################################################################
    # who-viewed
    ############################################################################

    public function whoViewedAction($object_type, $object_id, $view_action = 1)
    {
        $id_to_info = $this->db->fetchAllKeyed('
            SELECT person_id, date_created, COUNT(*) AS count
            FROM page_view_log
            WHERE object_type = ? AND object_id = ? AND view_action = ? AND person_id IS NOT NULL
            GROUP BY person_id
            ORDER BY id DESC
        ', [$object_type, $object_id, $view_action], 'person_id');

        $people = $this->em->getRepository('DeskPRO:Person')->getByIds(array_keys($id_to_info));

        return $this->render('AgentBundle:Publish:who-viewed.html.twig', [
            'id_to_info'  => $id_to_info,
            'people'      => $people,
            'object_type' => $object_id,
            'object_id'   => $object_id,
            'view_action' => $view_action,
        ]);
    }
}
