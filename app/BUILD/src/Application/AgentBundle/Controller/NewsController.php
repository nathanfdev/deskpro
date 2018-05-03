<?php

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\NewsResults;
use Application\AgentBundle\Form\Model\NewNews;
use Application\AgentBundle\Form\Type\NewNews as NewNewsType;
use Application\AgentBundle\Validator\NewNewsValidator;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\NewsComment;
use Application\DeskPRO\Entity\NewsRevision;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Entity\SearchStickyResult;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use DateTime;
use Doctrine\DBAL\Connection;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles listing and editing of news.
 */
class NewsController extends AbstractController
{
    //###########################################################################
    // view
    //###########################################################################

    public function viewAction($news_id)
    {
        /** @var News $news */
        $news = $this->em->find(News::class, $news_id);

        if (!$news) {
            throw $this->createNotFoundException();
        }

        $news_comments = $this->em->getRepository(NewsComment::class)->getComments($news);

        $related_finder  = new RelatedContentFinder($this->person, $news);
        $related_content = $related_finder->getRelatedEntities(true);

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.editnews', $this->person->id);

        $sticky_search_words = $this->em->getRepository(SearchStickyResult::class)->getWordsForObject($news);
        $rated_searches      = $this->em->getRepository(SearchLog::class)->getRatedSearchesFor('news', $news['id'], 'counted');

        if ($news->getCategory() && $news->getCategory()->getBrand()) {
            $brandId = $news->getCategory()->getBrand()->getId();
        } else {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }
        $news_categories = $this->getFilteredCategory($brandId);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        $perms = [
            'can_edit'   => $this->person->PermissionsManager->PublishChecker->canEdit($news),
            'can_delete' => $this->person->PermissionsManager->PublishChecker->canDelete($news),
        ];

        return $this->render('AgentBundle:News:view.html.twig', [
            'news'                => $news,
            'news_comments'       => $news_comments,
            'news_categories'     => $news_categories,
            'related_content'     => $related_content,
            'state'               => $state,
            'sticky_search_words' => $sticky_search_words,
            'rated_searches'      => $rated_searches,
            'perms'               => $perms,
            'brands'              => $brands,
        ]);
    }

    public function viewRevisionsAction($news_id)
    {
        $news = $this->em->find(News::class, $news_id);

        return $this->render('AgentBundle:News:view-revisions-tab.html.twig', [
            'news' => $news,
        ]);
    }

    public function ajaxSaveLabelsAction($news_id)
    {
        /** @var News $news */
        $news = $this->em->find(News::class, $news_id);

        if (!$news) {
            throw $this->createNotFoundException();
        }
        if (!$this->person->PermissionsManager->PublishChecker->canEdit($news)) {
            return $this->createJsonResponse(['success' => 0]);
        }

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $news->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($news);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    public function ajaxSaveCommentAction($news_id)
    {
        $news = $this->em->find(News::class, $news_id);

        if (!$news || !$this->in->getString('content')) {
            throw $this->createNotFoundException();
        }

        $comment                 = new NewsComment();
        $comment->news           = $news;
        $comment->person         = $this->person;
        $comment['content']      = $this->in->getString('content');
        $comment['status']       = 'visible';
        $comment['date_created'] = new DateTime();

        if ($this->person->hasPerm('agent_publish.validate')) {
            $comment->is_reviewed = true;
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $this->render('AgentBundle:News:view-comment.html.twig', [
            'comment' => $comment,
        ]);
    }

    public function ajaxSaveAction($news_id)
    {
        $news = $this->em->find(News::class, $news_id);
        $rev  = null;

        if (!$news) {
            throw $this->createNotFoundException();
        }

        $action = $this->in->getString('action');

        if ($action == 'delete') {
            if (!$this->person->PermissionsManager->PublishChecker->canDelete($news)) {
                return $this->createJsonResponse(['success' => false]);
            }
        } else {
            if (!$this->person->PermissionsManager->PublishChecker->canEdit($news)) {
                return $this->createJsonResponse(['success' => false]);
            }
        }

        $data = ['success' => 1];

        $this->em->beginTransaction();

        switch ($action) {
            case 'status':
                $news['status_code'] = $this->in->getString('status');
                if ($news['status_code'] == 'published' && !$this->person->hasPerm('agent_publish.validate')) {
                    $news['status_code'] = 'hidden.unpublished';
                }
                break;

            case 'title':
                $news['title'] = $this->in->getString('title');
                $rev           = ContentRevisionUtil::findOrCreate($news, 'title', $this->person);
                $rev['title']  = $news['title'];
                break;

            case 'slug':
                $news['slug'] = Strings::slugifyTitle($this->in->getString('slug')) ?: 'view';
                $data['slug'] = $news['slug'];
                break;

            case 'add-related':
                $updater = new RelatedContentUpdate($news);
                $updater->addRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-related':
                $updater = new RelatedContentUpdate($news);
                $updater->removeRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'content':

                $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.editnews', $this->person->id);

                $news['content'] = $this->person->hasPerm('agent_publish.can_insert_html')
                    ? $this->in->getCleanValue('content', 'string', null, ['noclean' => true])
                    : $this->in->getCleanValue('content', 'html');

                $data['content_html'] = $this->renderView('AgentBundle:News:view-content-tab.html.twig', [
                    'news' => $news,
                ]);

                $rev            = ContentRevisionUtil::findOrCreate($news, 'content', $this->person);
                $rev['content'] = $news['content'];

                break;

            case 'category':
                $cat                 = $this->em->find(NewsCategory::class, $this->in->getUInt('category_id'));
                $news['category']    = $cat;
                $data['category_id'] = $cat['id'];
                break;

            case 'delete':
                $news->status_code = 'hidden.deleted';
                break;

            case 'undelete':
                $news->status_code = 'published';
                break;

            case 'auto-unpub':
                $date   = date_create('@'.$this->in->getUInt('end_timestamp'));
                $action = $this->in->getString('end_action');

                $news->date_end   = $date;
                $news->end_action = $action;
                break;

            case 'remove-auto-unpub':
                $news->date_end   = null;
                $news->end_action = null;
                break;

            case 'auto-pub':
                $date = date_create('@'.$this->in->getUInt('pub_timestamp'));

                $news->date_published = $date;
                break;

            case 'remove-auto-pub':
                $news->date_published = null;
                break;
        }

        $this->em->persist($news);

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

    public function ajaxGetCategoriesByBrandAction($brand_id)
    {
        $categories = $this->getFilteredCategory($brand_id);

        return $this->render('AgentBundle:Common:select-standard.html.twig', [
            'name'             => 'newnews[category_id]',
            'id'               => '_cat',
            'add_classname'    => 'category_id',
            'add_attr'         => '',
            'with_blank'       => 0,
            'blank_title'      => '',
            'categories'       => $categories,
            'allow_parent_sel' => true,
        ]);
    }

    //###########################################################################
    // Compare revisions
    //###########################################################################

    public function compareRevisionsAction($rev_old_id, $rev_new_id)
    {
        $diff_info = ContentRevisionUtil::compareRevisions(NewsRevision::class, $rev_old_id, $rev_new_id);

        return $this->render('AgentBundle:News:compare-revs.html.twig', [
            'rendered_content_diff' => $diff_info['rendered_content_diff'],
            'rendered_title_diff'   => $diff_info['rendered_title_diff'],
        ]);
    }

    //###########################################################################
    // list
    //###########################################################################

    /**
     * View a list of feedback.
     */
    public function listAction($category_id = 0)
    {
        $category = null;
        if ($category_id) {
            /** @var NewsCategory $category */
            $category = $this->em->find(NewsCategory::class, $category_id);
        }

        $showAll = false;
        if (!$category) {
            $showAll = $this->in->getBool('all');
        }

        $resultHelper = NewsResults::newFromRequest($this, [
            'category' => $category,
            'show_all' => $showAll,
        ]);

        $page = $this->in->getUInt('p');
        if (!$page) {
            $page = 1;
        }

        $results     = $resultHelper->getNewsForPage($page);
        $resultCache = $resultHelper->getResultCache();

        $totalResults = count($resultHelper->getNewsIds());
        $numPages     = ceil($totalResults / 50);
        $showingTo    = min(($page) * 50, $totalResults);

        $displayFields = $this->person->getPref('agent.ui.news-filter-display-fields.0');
        if (!$displayFields) {
            $displayFields = ['author', 'date_created'];
        }

        $tpl = 'AgentBundle:News:filter.html.twig';
        if (@$_REQUEST['_partial']) {
            $tpl = 'AgentBundle:News:filter-page.html.twig';
        }

        $brandId        = $category->getBrand()->getId();
        $newsCategories = $this->getFilteredCategory($brandId);

        $commentCounts = [];
        if ($results) {
            $commentCounts = $this->db->fetchAllKeyValue('
                SELECT article_id, COUNT(*)
                FROM article_comments
                WHERE article_id IN (?)
                GROUP BY article_id
            ', [array_keys($results)], [Connection::PARAM_INT_ARRAY]);
        }

        $catUserGroups    = [];
        $catStructureData = [];
        if ($category) {
            $catUserGroups = $this->db->fetchAllCol('
                SELECT usergroup_id
                FROM news_category2usergroup
                WHERE category_id = ?
            ', [$category->getId()]);

            $catStructureData = $newsCategories;
            $catStructureData = Arrays::removeButKey($catStructureData, ['id', 'title', 'children'], true, true);
            $catStructureData = Arrays::multiRenameKey($catStructureData, 'title', 'label');
            $catStructureData = Arrays::assocToNumericArray($catStructureData, 'children');
        }

        return $this->render($tpl, [
            'results'            => $results,
            'result_id'          => $resultCache['id'],
            'comment_counts'     => $commentCounts,
            'display_fields'     => $displayFields,
            'category'           => $category,
            'cat_usergroups'     => $catUserGroups,
            'cat_structure_data' => $catStructureData,
            'total_results'      => $totalResults,
            'num_pages'          => $numPages,
            'cur_page'           => $page,
            'showing_to'         => $showingTo,
        ]);
    }

    //###########################################################################
    // New news
    //###########################################################################

    public function newNewsAction()
    {
        $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');

        $rootCategories = $this->getFilteredCategory($brandId);

        if (count($rootCategories) === 0) {
            $brands = $this->em->getRepository(Brand::class)->findAll();
            $brand  = array_shift($brands);
            while (count($rootCategories) === 0 && $brand->getId()) {
                $brandId        = $brand->getId();
                $rootCategories = $this->getFilteredCategory($brandId);
                $brand          = array_shift($brands);
            }
        }

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.newnews', $this->person->id);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:News:newnews.html.twig', [
            'news_categories'   => $rootCategories,
            'state'             => $state,
            'brands'            => $brands,
            'selected_brand_id' => $brandId,
        ]);
    }

    public function newNewsSaveAction(Request $request)
    {
        $newNews = new NewNews($this->person);

        $formType = new NewNewsType();
        $form     = $this->get('form.factory')->create($formType, $newNews);

        $this->db->executeUpdate("DELETE FROM people_prefs WHERE name = 'agent.ui.state.newnews' AND person_id = ?", [$this->person->id]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $form->isValid();

            $validator = new NewNewsValidator();
            if (!$validator->isValid($newNews)) {
                return $this->createJsonResponse([
                    'error'       => true,
                    'error_codes' => $validator->getErrorGroups(),
                ]);
            }
            $newNews->save();

            $news = $newNews->getNews();

            $rev = ContentRevisionUtil::findOrCreate($news, ['title', 'content'], $this->person);
            if ($rev) {
                $this->em->persist($rev);
                $this->em->flush();
            }

            $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.newnews', $this->person->id);

            return $this->createJsonResponse([
                'success' => true,
                'news_id' => $news['id'],
            ]);
        } else {
            return $this->createJsonResponse([
                'success' => false,
            ]);
        }
    }

    /**
     * @param $brandId
     *
     * @return array
     */
    private function getFilteredCategory($brandId)
    {
        $unFilteredCategories = $this->em->getRepository(NewsCategory::class)->getInHierarchy();

        $newsCategories = [];

        foreach ($unFilteredCategories as $c) {
            if ($brandId == $c['brand_id']) {
                $newsCategories[] = $c;
            }
        }

        return $newsCategories;
    }
}
