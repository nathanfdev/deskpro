<?php

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\DownloadResults;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadCategory;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Entity\DownloadRevision;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Entity\SearchStickyResult;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use Doctrine\DBAL\Connection;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Util\Web;
use Symfony\Component\HttpFoundation\Request;

class DownloadsController extends AbstractController
{
    //###########################################################################
    // view
    //###########################################################################

    public function viewAction($download_id)
    {
        /** @var Download $download */
        $download = $this->em->find(Download::class, $download_id);

        if (!$download) {
            throw $this->createNotFoundException();
        }

        $downloadComments = $this->em->getRepository(DownloadComment::class)->getComments($download);

        $relatedFinder  = new RelatedContentFinder($this->person, $download);
        $relatedContent = $relatedFinder->getRelatedEntities(true);

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.editdownload', $this->person->id);

        $stickySearchWords = $this->em->getRepository(SearchStickyResult::class)->getWordsForObject($download);

        $ratedSearches = $this->em->getRepository(SearchLog::class)->getRatedSearchesFor('download', $download['id'], 'counted');

        if ($download->getCategory() && $download->getCategory()->getBrand()) {
            $brandId = $download->getCategory()->getBrand()->getId();
        } else {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }
        $downloadCategories = $this->getFilteredCategory($brandId);

        $perms = [
            'can_edit'   => $this->person->PermissionsManager->PublishChecker->canEdit($download),
            'can_delete' => $this->person->PermissionsManager->PublishChecker->canDelete($download),
        ];

        $userViewCount = $this->db->fetchColumn('
            SELECT COUNT(*)
            FROM page_view_log
            WHERE object_type = 2 AND object_id = ? AND view_action = 1 AND person_id IS NOT NULL
        ', [$download->id]);

        $userDownloadCount = $this->db->fetchColumn('
            SELECT COUNT(*)
            FROM page_view_log
            WHERE object_type = 2 AND object_id = ? AND view_action = 2 AND person_id IS NOT NULL
        ', [$download->id]);

        return $this->render('AgentBundle:Downloads:view.html.twig', [
            'download'            => $download,
            'download_comments'   => $downloadComments,
            'download_categories' => $downloadCategories,
            'related_content'     => $relatedContent,
            'state'               => $state,
            'sticky_search_words' => $stickySearchWords,
            'rated_searches'      => $ratedSearches,
            'perms'               => $perms,
            'user_view_count'     => $userViewCount,
            'user_download_count' => $userDownloadCount,
        ]);
    }

    public function infoAction($download_id)
    {
        $download = $this->em->find(Download::class, $download_id);
        $blob     = $download->getBlob();

        $data = [
            'blob_id'           => $blob['id'],
            'download_url'      => $blob->getDownloadUrl(true),
            'filename'          => $blob['filename'],
            'filesize_readable' => $blob->getReadableFilesize(),
            'permalink'         => $this->get('object_router')->getPortalUrl($download),
        ];

        return $this->createJsonResponse($data);
    }

    public function viewRevisionsAction($download_id)
    {
        $download = $this->em->find(Download::class, $download_id);

        return $this->render('AgentBundle:Downloads:view-revisions-tab.html.twig', [
            'download' => $download,
        ]);
    }

    public function ajaxSaveLabelsAction($download_id)
    {
        $download = $this->em->find(Download::class, $download_id);

        if (!$download || !$this->person->PermissionsManager->PublishChecker->canEdit($download)) {
            throw $this->createNotFoundException();
        }

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $download->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($download);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    public function ajaxSaveCommentAction($download_id)
    {
        $download = $this->em->find(Download::class, $download_id);

        if (!$download || !$this->in->getString('content')) {
            throw $this->createNotFoundException();
        }

        $comment                 = new DownloadComment();
        $comment->download       = $download;
        $comment->person         = $this->person;
        $comment['content']      = $this->in->getString('content');
        $comment['status']       = 'visible';
        $comment['date_created'] = new \DateTime();

        if ($this->person->hasPerm('agent_publish.validate')) {
            $comment->is_reviewed = true;
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $this->render('AgentBundle:Downloads:view-comment.html.twig', [
            'comment' => $comment,
        ]);
    }

    public function ajaxSaveAction($download_id)
    {
        $download = $this->em->find(Download::class, $download_id);
        $rev      = null;

        if (!$download) {
            throw $this->createNotFoundException();
        }

        $action = $this->in->getString('action');

        if ($action == 'delete') {
            if (!$this->person->PermissionsManager->PublishChecker->canDelete($download)) {
                return $this->createJsonResponse(['success' => false]);
            }
        } else {
            if (!$this->person->PermissionsManager->PublishChecker->canEdit($download)) {
                return $this->createJsonResponse(['success' => false]);
            }
        }

        $data = ['success' => 1];

        $this->em->beginTransaction();

        switch ($action) {

            case 'status':
                $download['status_code'] = $this->in->getString('status');
                if ($download['status_code'] == 'published' && !$this->person->hasPerm('agent_publish.validate')) {
                    $download['status_code'] = 'hidden.unpublished';
                }
                break;

            case 'delete':
                $download['status_code'] = 'hidden.deleted';
                break;

            case 'undelete':
                $download['status_code'] = 'published';
                break;

            case 'title':
                $download['title'] = $this->in->getString('title');
                $rev               = ContentRevisionUtil::findOrCreate($download, 'title', $this->person);
                $rev['title']      = $download['title'];
                break;

            case 'slug':
                $download['slug'] = Strings::slugifyTitle($this->in->getString('slug')) ?: 'view';
                $data['slug']     = $download['slug'];
                break;

            case 'add-related':
                $updater = new RelatedContentUpdate($download);
                $updater->addRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-related':
                $updater = new RelatedContentUpdate($download);
                $updater->removeRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'file':

                $rev = ContentRevisionUtil::findOrCreate($download, ['blob', 'title'], $this->person);

                if ($this->in->getUInt('download.attach') && $blob = $this->em->getRepository(Blob::class)->find($this->in->getUInt('download.attach'))) {
                    $title = $this->in->getString('download.title') ?: $title = $blob->getFilename();
                    $download->setBlob($blob)->setTitle($title);
                    /* @var Blob $blob */
                    $blob->setIsTemp(false)->setFilename($title);
                    $this->em->persist($blob);

                    $rev['title'] = $title;
                    $rev->blob    = $download->getBlob();
                } elseif ($this->in->getString('download.fileurl')) {
                    $fileurl  = $this->in->getString('download.fileurl');
                    $filesize = $this->in->getString('download.filesize');
                    $filename = $this->in->getString('download.filename');

                    if (!$filename) {
                        $filename = Web::getUrlFileName($fileurl);
                        if (!$filename) {
                            $filename = '';
                        }
                    }
                    if (!$filesize) {
                        $filesize = Web::getUrlFileSize($fileurl);
                        if (!$filesize) {
                            $filesize = 0;
                        }
                    }

                    $download->setFileUrl(
                        $fileurl,
                        $filesize,
                        $filename
                    );
                }

                $data['file_html'] = $this->renderView('AgentBundle:Downloads:view-fileinfo.html.twig', [
                    'download' => $download,
                ]);

                break;

            case 'content':

                $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.editdownload', $this->person->id);

                $changed_content = false;
                if ($this->in->getString('content') != $download['content']) {
                    $changed_content     = true;
                    $download['content'] = $this->person->hasPerm('agent_publish.can_insert_html')
                        ? $this->in->getCleanValue('content', 'string', null, ['noclean' => true])
                        : $this->in->getCleanValue('content', 'html');
                }

                $inlineBlobIds = $this->in->getCleanValueArray('blob_inline_ids', 'int');
                $this->get('attachment_helper')->processInlineBlobs($download['content'], $inlineBlobIds);

                $data['content_html'] = $this->renderView('AgentBundle:Downloads:view-content-tab.html.twig', [
                    'download' => $download,
                ]);

                $rev = ContentRevisionUtil::findOrCreate($download, ['content'], $this->person);

                if ($changed_content) {
                    $rev['content'] = $download['content'];
                }

                break;

            case 'category':
                $cat                  = $this->em->find(DownloadCategory::class, $this->in->getUInt('category_id'));
                $download['category'] = $cat;
                $data['category_id']  = $cat['id'];
                break;
        }

        $this->em->persist($download);

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
            'name'             => 'newdownload[category_id]',
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
        $diff_info = ContentRevisionUtil::compareRevisions(DownloadRevision::class, $rev_old_id, $rev_new_id);

        return $this->render('AgentBundle:Downloads:compare-revs.html.twig', [
            'rendered_content_diff' => $diff_info['rendered_content_diff'],
            'rendered_title_diff'   => $diff_info['rendered_title_diff'],
            'new_blob'              => !empty($diff_info['new_blob']) ? $diff_info['new_blob'] : null,
            'old_blob'              => !empty($diff_info['old_blob']) ? $diff_info['old_blob'] : null,
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
            $category = $this->em->find(DownloadCategory::class, $category_id);
        }

        $showAll = false;
        if (!$category) {
            $showAll = $this->in->getBool('all');
        }

        $result_helper = DownloadResults::newFromRequest($this, [
            'category' => $category,
            'show_all' => $showAll,
        ]);

        $page = $this->in->getUInt('p');
        if (!$page) {
            $page = 1;
        }

        $results     = $result_helper->getDownloadsForPage($page);
        $resultCache = $result_helper->getResultCache();

        $totalResults = count($result_helper->getDownloadIds());
        $numPages     = ceil($totalResults / 50);
        $showingTo    = min(($page) * 50, $totalResults);

        $displayFields = $this->person->getPref('agent.ui.download-filter-display-fields.0');
        if (!$displayFields) {
            $displayFields = ['author', 'date_created'];
        }

        $tpl = 'AgentBundle:Downloads:filter.html.twig';
        if (@$_REQUEST['_partial']) {
            $tpl = 'AgentBundle:Downloads:filter-page.html.twig';
        }

        if ($category) {
            $brandId = $category->getBrand()->getId();
        } else {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }

        $downloadCategories = $this->getFilteredCategory($brandId);

        $commentCounts = [];
        if ($results) {
            $commentCounts = $this->db->fetchAllKeyValue('
                SELECT download_id, COUNT(*)
                FROM download_comments
                WHERE download_id IN (?)
                GROUP BY download_id
            ', [array_keys($results)], [Connection::PARAM_INT_ARRAY]);
        }

        $catUserGroups    = [];
        $catStructureData = [];
        if ($category) {
            $catUserGroups = $this->db->fetchAllCol('
                SELECT usergroup_id
                FROM download_category2usergroup
                WHERE category_id = ?
            ', [$category->getId()]);

            $catStructureData = $downloadCategories;
            $catStructureData = Arrays::removeButKey($catStructureData, ['id', 'title', 'children'], true, true);
            $catStructureData = Arrays::multiRenameKey($catStructureData, 'title', 'label');
            $catStructureData = Arrays::assocToNumericArray($catStructureData, 'children');
        }

        $perms = [
            'can_edit'   => $this->person->PermissionsManager->PublishChecker->canEdit($category),
            'can_delete' => $this->person->PermissionsManager->PublishChecker->canDelete($category),
        ];

        return $this->render($tpl, [
            'results'            => $results,
            'comment_counts'     => $commentCounts,
            'result_id'          => $resultCache['id'],
            'cache'              => $resultCache,
            'display_fields'     => $displayFields,
            'category'           => $category,
            'cat_usergroups'     => $catUserGroups,
            'cat_structure_data' => $catStructureData,
            'total_results'      => $totalResults,
            'num_pages'          => $numPages,
            'cur_page'           => $page,
            'showing_to'         => $showingTo,
            'perms'              => $perms,
        ]);
    }

    //###########################################################################
    // New download
    //###########################################################################

    public function newDownloadAction()
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

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.newdownload', $this->person->id);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:Downloads:newdownload.html.twig', [
            'download_categories' => $rootCategories,
            'state'               => $state,
            'brands'              => $brands,
            'selected_brand_id'   => $brandId,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newDownloadSaveAction(Request $request)
    {
        $newdownload = new \Application\AgentBundle\Form\Model\NewDownload($this->person);

        $formType = new \Application\AgentBundle\Form\Type\NewDownload();
        $form     = $this->get('form.factory')->create($formType, $newdownload);

        $this->db->executeUpdate("DELETE FROM people_prefs WHERE name = 'agent.ui.state.newdownload' AND person_id = ?", [$this->person->id]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $form->isValid();

            $validator = new \Application\AgentBundle\Validator\NewDownloadValidator();
            if (!$validator->isValid($newdownload)) {
                return $this->createJsonResponse([
                    'error'       => true,
                    'error_codes' => $validator->getErrorGroups(),
                ]);
            }
            $newdownload->save();

            $download = $newdownload->getDownload();

            $rev = ContentRevisionUtil::findOrCreate($download, ['title', 'content'], $this->person);
            if ($rev) {
                $this->em->persist($rev);
                $this->em->flush();
            }

            $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.newdownload', $this->person->id);

            return $this->createJsonResponse([
                'success'     => true,
                'download_id' => $download['id'],
            ]);
        } else {
            return $this->createJsonResponse([
                'success' => false,
            ]);
        }
    }

    /**
     * @param int $brandId
     *
     * @return array
     */
    private function getFilteredCategory($brandId)
    {
        $unFilteredCategories = $this->em->getRepository(DownloadCategory::class)->getInHierarchy();

        $downloadCategories = [];

        foreach ($unFilteredCategories as $c) {
            if ($brandId == $c['brand_id']) {
                $downloadCategories[] = $c;
            }
        }

        return $downloadCategories;
    }
}
