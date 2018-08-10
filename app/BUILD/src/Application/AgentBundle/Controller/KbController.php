<?php

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\ArticleResults;
use Application\DeskPRO\App;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Entity\ArticleRevision;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\SearchLog;
use Application\DeskPRO\Entity\SearchStickyResult;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Publish\GlossaryHandler;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use Doctrine\DBAL\Connection;
use Orb\Data\ContentTypes;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles ticket searches.
 */
class KbController extends AbstractController
{
    //###########################################################################
    // Edit article
    //###########################################################################

    public function viewArticleAction($article_id, Request $request)
    {
        $isPdf = $this->in->getBool('pdf');
        /** @var Article $article */
        $article = $this->em->find(Article::class, $article_id);
        if (!$article) {
            throw $this->createNotFoundException("Unknown article $article_id");
        }

        if ($request->get('do_validate') and $article['status_code'] == 'hidden.unpublished' && $this->person->hasPerm('agent_publish.validate')) {
            $article['status_code'] = Article::STATUS_PUBLISHED;
            $this->em->persist($article);
            $this->em->flush();
        }

        $tpl = 'AgentBundle:Kb:view.html.twig';

        //------------------------------
        // Custom fields
        //------------------------------

        $field_manager = $this->container->getSystemService('article_fields_manager');
        $custom_fields = $field_manager->getDisplayArrayForObject($article);

        //------------------------------
        // Article props
        //------------------------------

        $article_comments = $this->em->getRepository(ArticleComment::class)->getComments($article);

        $article_revisions = $article->getRevisions();

        $related_finder  = new RelatedContentFinder($this->person, $article);
        $related_content = $related_finder->getRelatedEntities(true);

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.editarticle.'.$article->getId(), $this->person->id);

        $sticky_search_words = $this->em->getRepository(SearchStickyResult::class)->getWordsForObject($article);

        $rated_searches = $this->em->getRepository(SearchLog::class)->getRatedSearchesFor('article', $article['id'], 'counted');

        if (!$category = $article->getPrimaryCategory()) {
            $category = current($article->getCategories());
        }
        if ($category && $category->getBrand()) {
            $brand   = $category->getBrand();
            $brandId = $brand->getId();
        } else {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
            $brand   = $this->em->getRepository(Brand::class)->find($brandId);
        }
        $article_categories = $this->getFilteredCategory($brandId);
        $article_products   = $this->em->getRepository(Product::class)->getInHierarchy();

        $perms = [
            'can_edit'   => $this->person->PermissionsManager->PublishChecker->canEdit($article),
            'can_delete' => $this->person->PermissionsManager->PublishChecker->canDelete($article),
        ];

        $user_view_count = $this->db->fetchColumn('
            SELECT COUNT(*)
            FROM page_view_log
            WHERE object_type = 1 AND object_id = ? AND view_action = 1 AND person_id IS NOT NULL
        ', [$article->id]);

        // Existing translations
        $trans_langs   = $this->db->fetchAllCol('SELECT language_id FROM object_lang WHERE ref = ?', ['articles.'.$article['id']]);
        $trans_langs[] = $article->language->getId();
        $trans_langs   = array_combine($trans_langs, $trans_langs);

        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $this->container->getObjectLangRepository()->preloadObject($lang, $article);
        }
        $this->container->getObjectLangRepository()->runPreload();

        $trans_data = $this->container->getObjectLangRepository()->getLoadedRecs($article);

        if (!count($article->getCategories()) && $article_categories) {
            $first = Arrays::getFirstKey($article_categories);
            if ($category = $this->em->getRepository(ArticleCategory::class)->find($first)) {
                $article->addToCategory($category);
                $this->em->persist($article);
                $this->em->flush();
            }
        }

        $glossary       = new GlossaryHandler($this->em, $brand);
        $glossary_words = $glossary->findWords($article->content);
        $word_defs      = $glossary->getWordDefs($glossary_words);

        $vars = [
            'article'             => $article,
            'trans_langs'         => $trans_langs,
            'trans_data'          => $trans_data,
            'custom_fields'       => $custom_fields,
            'sticky_search_words' => $sticky_search_words,
            'rated_searches'      => $rated_searches,
            'content'             => $article->content,
            'article_comments'    => $article_comments,
            'article_revisions'   => $article_revisions,
            'related_content'     => $related_content,
            'state'               => $state,
            'article_categories'  => $article_categories,
            'article_products'    => $article_products,
            'glossary_words'      => $glossary_words,
            'perms'               => $perms,
            'user_view_count'     => $user_view_count,

            'word_defs' => $word_defs,
        ];

        if ($isPdf) {
            $contentHtml = $this->renderView('DeskPRO:pdf_agent:view_article.html.twig', $vars);

            if ($this->in->getBool('html')) {
                $response = new Response();
                $response->setContent($contentHtml);
            } else {
                $pdfRenderer = $this->get('pdf_renderer');

                $pdfRenderer->generateFile($contentHtml, $article->title.'.pdf');
                exit;
            }
        }

        return $this->render($tpl, $vars);
    }

    public function viewRevisionsAction($article_id)
    {
        $article = $this->em->find(Article::class, $article_id);
        if (!$article) {
            throw $this->createNotFoundException("Unknown article $article_id");
        }

        $article_revisions = $article->getRevisions();

        return $this->render('AgentBundle:Kb:view-revisions-tab.html.twig', [
            'article'           => $article,
            'article_revisions' => $article_revisions,
        ]);
    }

    public function ajaxSaveLabelsAction($article_id)
    {
        if (!$article = $this->em->find(Article::class, $article_id)) {
            throw $this->createNotFoundException();
        }

        if (!$this->person->PermissionsManager->PublishChecker->canEdit($article)) {
            return $this->createJsonResponse(['success' => 0]);
        }

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $article->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($article);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    public function ajaxMassSaveAction()
    {
        $articles      = $this->in->getCleanValueArray('result_ids', 'int', 'discard');
        $from_category = $this->in->getInt('from_category');
        $action        = $this->in->getString('action');

        $data  = ['success' => 1, 'category' => $from_category];
        $skip  = false;
        $tr    = App::getTranslator();
        $error = null;

        switch ($action) {
            case 'move':
                $to_category = $this->in->getInt('to_category');

                if ($from_category && $from_category == $to_category) {
                    $error = $tr->phrase('agent.publish.error_kb_cats_same');
                    $skip  = true;
                    break;
                }

                if ($from_category) {
                    $from = $this->em->find(ArticleCategory::class, $from_category);
                } else {
                    $from = null;
                }

                $to = $this->em->find(ArticleCategory::class, $to_category);

                if (($from_category && !$from) || !$to) {
                    $error = $tr->phrase('agent.publish.error_kb_not_in_db');
                    $skip  = true;
                    break;
                }

                break;
        }

        if (!$skip) {
            $affected      = 0;
            $perm_failures = 0;
            $missing       = 0;
            $this->em->beginTransaction();

            foreach ($articles as $article_id) {
                $article = $this->em->find(Article::class, $article_id);

                if (!$article) {
                    ++$missing;
                    continue;
                }

                switch ($action) {
                    case 'draft':
                        if (!$this->person->PermissionsManager->PublishChecker->canEdit($article)) {
                            ++$perm_failures;
                            continue;
                        }

                        $article->status_code = 'hidden.draft';
                        ++$affected;
                        break;
                    case 'delete':
                        if (!$this->person->PermissionsManager->PublishChecker->canDelete($article)) {
                            ++$perm_failures;
                            continue;
                        }

                        $article->status_code = 'hidden.deleted';
                        ++$affected;
                        break;
                    case 'move':
                        if (!$this->person->PermissionsManager->PublishChecker->canEdit($article)) {
                            ++$perm_failures;
                            continue;
                        }

                        if ($from) {
                            $article->removeFromCategory($from);
                        } else {
                            // If theres no 'from' category, means we're
                            // moving from all so delete all old cats
                            foreach ($article->categories as $c) {
                                if ($c->getId() != $to->getId()) {
                                    $article->removeFromCategory($c);
                                }
                            }
                        }

                        if (!$article->isInCategory($to)) {
                            $article->addToCategory($to);
                        }

                        ++$affected;
                        break;
                }

                $this->em->persist($article);
            }

            $this->em->flush();
            $this->em->commit();

            if ($affected < count($articles)) {
                $error = $tr->phrase('agent.publish.error_kb_unaffected');
                $error .= "<br />\n";

                $errors = [];

                if ($missing) {
                    $errors[] = $tr->phrase('agent.publish.error_kb_missing', ['count' => $missing]);
                }

                if ($perm_failures) {
                    $errors[] = $tr->phrase('agent.publish.error_kb_perm_denied', ['count' => $perm_failures]);
                }

                $error .= implode("<br />\n", $errors);
            }
        } else {
            $data['success'] = false;
        }

        $data['error'] = $error;

        return $this->createJsonResponse($data);
    }

    public function ajaxSaveAction($article_id)
    {
        $article = $this->em->find(Article::class, $article_id);

        if (!$article) {
            throw $this->createNotFoundException();
        }

        $rev = null;

        $action = $this->in->getString('action');

        if ($action == 'delete') {
            if (!$this->person->PermissionsManager->PublishChecker->canDelete($article)) {
                return $this->createJsonResponse(['success' => false]);
            }
        } else {
            if (!$this->person->PermissionsManager->PublishChecker->canEdit($article)) {
                return $this->createJsonResponse(['success' => false]);
            }
        }

        $data = ['success' => 1];

        $this->em->beginTransaction();

        switch ($action) {
            case 'status':
                $article['status_code'] = $this->in->getString('status');
                if ($article['status_code'] == 'published' && !$this->person->hasPerm('agent_publish.validate')) {
                    $article['status_code'] = 'hidden.unpublished';
                }
                break;

            case 'title':
                $article['title'] = $this->in->getString('title');
                $rev              = ContentRevisionUtil::findOrCreate($article, 'title', $this->person);
                $rev['title']     = $article['title'];
                break;

            case 'slug':
                $article->setSlug(Strings::slugifyTitle($this->in->getString('slug')) ?: 'view');
                $data['slug'] = $article['slug'];
                break;

            case 'delete':
                $article->status_code = 'hidden.deleted';
                break;

            case 'undelete':
                $article->status_code = 'published';
                break;

            case 'categories':
                $catIds = $this->in->getCleanValueArray('category_ids', 'uint', 'discard');
                $cats   = $this->em->getRepository(ArticleCategory::class)->getByIds($catIds);

                $article->setCategories($cats);

                $data['category_ids'] = $catIds;
                break;

            case 'products':
                $prodIds = $this->in->getCleanValueArray('product_ids', 'uint', 'discard');
                $prods   = $this->em->getRepository(Product::class)->getByIds($prodIds);

                $article->setProducts($prods);

                $data['product_ids'] = $prodIds;
                break;

            case 'remove-auto-unpub':
                $article->date_end   = null;
                $article->end_action = null;
                break;

            case 'auto-unpub':
                $date   = date_create('@'.$this->in->getUInt('end_timestamp'));
                $action = $this->in->getString('end_action');

                $article->date_end   = $date;
                $article->end_action = $action;
                break;

            case 'auto-pub':
                $date = date_create('@'.$this->in->getUInt('pub_timestamp'));

                $article->date_published = $date;
                break;

            case 'remove-auto-pub':
                $article->date_published = null;
                break;

            case 'add-related':
                $updater = new RelatedContentUpdate($article);
                $updater->addRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-related':
                $updater = new RelatedContentUpdate($article);
                $updater->removeRelated(
                    $this->in->getString('content_type'),
                    $this->in->getString('content_id')
                );
                break;

            case 'remove-blob':

                foreach ($article->attachments as $k => $attach) {
                    if ($attach->blob['id'] == $this->in->getUInt('blob_id')) {
                        $article->attachments->remove($k);
                        $this->em->remove($attach);
                        break;
                    }
                }

                break;

            case 'content':

                $content = $this->person->hasPerm('agent_publish.can_insert_html')
                    ? $this->in->getCleanValue('content', 'string', null, ['noclean' => true])
                    : $this->in->getCleanValue('content', 'html');

                $contentInfo = Strings::parseImageDataUrls($content);

                if (!empty($contentInfo['files'])) {
                    foreach ($contentInfo['files'] as $file_info) {
                        $fileExt = ContentTypes::findExtensionForContentType($file_info['type'], false);
                        if (!$fileExt) {
                            continue;
                        }

                        $blob = $this->container->getBlobStorage()->createBlobRecordFromString(
                            $file_info['data'],
                            "file.$fileExt",
                            $file_info['type'],
                            []
                        );
                        $blob->is_media_upload = true;

                        $this->em->persist($blob);

                        $contentInfo['string'] = str_replace($file_info['token'], $blob->getDownloadUrl(true, true), $contentInfo['string']);
                    }
                }

                $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.editarticle', $this->person->id);

                $article['content'] = $contentInfo['string'];

                $rev            = ContentRevisionUtil::findOrCreate($article, 'content', $this->person);
                $rev['content'] = $article['content'];

                if (!$category = $article->getPrimaryCategory()) {
                    $category = current($article->getCategories());
                }
                if ($category && $category->getBrand()) {
                    $brand = $category->getBrand();
                } else {
                    $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
                    $brand   = $this->container->getEm()->getRepository(Brand::class)->find($brandId);
                }

                $glossary = new GlossaryHandler($this->em, $brand);
                $content  = $article->content;
                $content  = $glossary->processText($content);

                $article->setContentInput($this->in->getStringRaw('content_input'));
                $article->setContentInputType($this->in->getString('content_input_type'));

                if ($langId = $this->in->getUInt('language_id')) {
                    $lang = $this->container->getLanguageData()->get($langId);
                    if ($lang) {
                        $article->language = $lang;
                    }
                }

                $article->date_updated = new \DateTime();

                $data['content_html'] = $this->renderView('AgentBundle:Kb:view-content-tab.html.twig', [
                    'article' => $article,
                    'content' => $content,
                ]);
                break;

            case 'trans':

                foreach ($this->container->getLanguageData()->getAll() as $lang) {
                    $this->container->getObjectLangRepository()->preloadObject($lang, $article);
                }

                foreach ($this->container->getLanguageData()->getAll() as $lang) {
                    $langId = $lang->getId();

                    if ($langId == $article->language->getId()) {
                        continue;
                    }

                    $title       = $this->in->getString("title.$langId");
                    $content_val = (string) $this->in->getRaw("content.$langId");

                    if (!$title && !$content_val) {
                        continue;
                    }

                    $rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'title', $title);
                    $this->em->persist($rec);

                    $rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'content', $content_val);
                    $this->em->persist($rec);
                }

                break;
        }

        $this->em->persist($article);
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

    public function ajaxSaveCustomFieldsAction($article_id)
    {
        $article = $this->em->find(Article::class, $article_id);

        if (!$article) {
            throw $this->createNotFoundException();
        }

        if (!$this->person->PermissionsManager->PublishChecker->canEdit($article)) {
            throw $this->createNotFoundException();
        }

        $this->em->beginTransaction();

        try {
            $field_manager      = $this->container->getSystemService('article_fields_manager');
            $post_custom_fields = $this->request->request->get('custom_fields', []);
            if (!empty($post_custom_fields)) {
                $field_manager->saveFormToObject($post_custom_fields, $article);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $custom_fields = $field_manager->getDisplayArrayForObject($article);

        return $this->render('AgentBundle:Kb:view-customfields-rendered-rows.html.twig', [
            'article'       => $article,
            'custom_fields' => $custom_fields,
        ]);
    }

    public function ajaxSaveCommentAction($article_id)
    {
        /** @var Article $article */
        $article = $this->em->find(Article::class, $article_id);

        if (!$article) {
            throw $this->createNotFoundException();
        }

        $comment = new ArticleComment();
        $comment->setObject($article);
        $comment->setPerson($this->person);
        $comment->setContent($this->in->getString('content'));
        $comment->setDateCreated(new \DateTime());

        if ($this->person->hasPerm('agent_publish.validate')) {
            $comment->setStatus(ArticleComment::STATUS_VISIBLE);
        } else {
            $comment->setStatus(ArticleComment::STATUS_HIDDEN);
        }

        $article->addComment($comment);

        $this->em->persist($comment);
        $this->em->flush();

        return $this->render('AgentBundle:Kb:view-comment.html.twig', [
            'comment' => $comment,
        ]);
    }

    public function ajaxGetCategoriesByBrandAction($brand_id)
    {
        $categories = $this->getFilteredCategory($brand_id);

        return $this->render('AgentBundle:Common:select-standard.html.twig', [
            'name'             => 'newarticle[category_id]',
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
    // Pending articles
    //###########################################################################

    /**
     * List the articles.
     */
    public function listPendingArticlesAction()
    {
        $pending_articles = $this->em->getRepository(ArticlePendingCreate::class)->getPendingArticles();

        $ticket_ids = [];
        foreach ($pending_articles as $pa) {
            if ($pa->getTicketId()) {
                $ticket_ids[] = $pa->getTicketId();
            }
        }

        $first_messages = [];
        if ($ticket_ids) {
            $first_messages_raw = $this->em->createQuery('
                SELECT m
                FROM DeskPRO:TicketMessage m
                LEFT JOIN m.ticket t
                WHERE t.id IN (?0)
                GROUP BY t
                ORDER BY m.id ASC
            ')->setParameters([$ticket_ids])->execute();

            $first_messages = [];
            foreach ($first_messages_raw as $m) {
                $first_messages[$m->ticket->getId()] = $m;
            }
        }

        return $this->render('AgentBundle:Kb:pending-articles.html.twig', [
            'pending_articles' => $pending_articles,
            'first_messages'   => $first_messages,
        ]);
    }

    /**
     * [AJAX] Adds a new pending article.
     */
    public function newPendingArticleAction()
    {
        $pending_article         = new ArticlePendingCreate();
        $pending_article->person = $this->person;

        if ($this->in->getUInt('ticket_id')) {
            $ticket = $this->em->find(Ticket::class, $this->in->getUInt('ticket_id'));
            if ($ticket) {
                $pending_article->ticket = $ticket;
            }
        }

        $pending_article['comment'] = $this->in->getString('comment');

        $this->em->persist($pending_article);
        $this->em->flush();

        $row_html = $this->renderView('AgentBundle:Kb:pending-articles-page.html.twig', ['pending_articles' => [$pending_article]]);

        return $this->createJsonResponse([
            'row_html'           => $row_html,
            'pending_article_id' => $pending_article['id'],
        ]);
    }

    /**
     * [AJAX] remove a pending article.
     */
    public function removePendingArticleAction($pending_article_id)
    {
        $pending_article = $this->em->find(ArticlePendingCreate::class, $pending_article_id);

        if (!$pending_article) {
            throw $this->createNotFoundException();
        }
        if (!$this->person->PermissionsManager->PublishChecker->canValidate($pending_article)) {
            return $this->createJsonResponse(['success' => false]);
        }

        $this->em->remove($pending_article);
        $this->em->flush();

        return $this->createJsonResponse([
            'success'            => true,
            'pending_article_id' => $pending_article_id,
        ]);
    }

    public function pendingArticleInfoAction($pending_article_id)
    {
        $pending_article = $this->em->find(ArticlePendingCreate::class, $pending_article_id);

        if (!$pending_article) {
            throw $this->createNotFoundException();
        }

        $data            = [];
        $data['id']      = $pending_article_id;
        $data['comment'] = $pending_article['comment'];

        $data['person_id']   = $pending_article->person['id'];
        $data['person_name'] = $pending_article->person->getDisplayName();

        $ticket = null;
        if ($pending_article->ticket) {
            $ticket                 = $pending_article->ticket;
            $data['ticket_id']      = $pending_article->ticket->id;
            $data['ticket_subject'] = $pending_article->ticket->subject;
            $data['ticket_url']     = $this->get('router')->generate('agent_ticket_view', ['ticket_id' => $pending_article->ticket->id]);
        }
        if ($pending_article->message) {
            $ticket                       = $pending_article->message->ticket;
            $data['message_id']           = $pending_article->message->id;
            $data['message_content_html'] = $pending_article->message->getMessageHtml();
        }

        // First message
        if ($ticket) {
            $first_message                = $this->em->getRepository(TicketMessage::class)->getFirstTicketMessage($ticket);
            $data['initial_message_html'] = $first_message->getMessageHtml();
            $data['initial_message_id']   = $first_message->id;
        }

        return $this->createJsonResponse($data);
    }

    public function pendingArticlesMassActionsAction($action)
    {
        $this->em->beginTransaction();

        $p_articles = $this->em->getRepository(ArticlePendingCreate::class)->getByIds($this->in->getCleanValueArray('ids', 'uint', 'discard'));

        foreach ($p_articles as $p_article) {
            switch ($action) {
                case 'delete':
                    if (!$this->person->PermissionsManager->PublishChecker->canValidate($p_article)) {
                        continue;
                    }
                    $this->em->remove($p_article);
                    break;
            }
        }

        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse([
            'success' => 1,
        ]);
    }

    //###########################################################################
    // Listings
    //###########################################################################

    public function listAction($category_id = 0)
    {
        $category = null;
        if ($category_id) {
            $category = $this->em->find(ArticleCategory::class, $category_id);
        }

        $showAll = false;
        if (!$category) {
            $showAll = $this->in->getBool('all');
        }

        $isTransView = false;
        $transLangId = null;

        if ($this->in->getBool('pending_translate')) {
            $isTransView = true;
            $transLangId = $this->in->getUInt('language_id');

            $brandId = $this->in->getUInt('brand_id');

            if (!$brandId) {
                $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
            }

            $resultHelper = ArticleResults::newFromRequest($this, [
                'pending_translate'      => true,
                'pending_translate_lang' => $this->in->getUInt('language_id'),
                'brand_id'               => $brandId,
            ]);
        } else {
            $resultHelper = ArticleResults::newFromRequest($this, [
                'category' => $category,
                'show_all' => $showAll,
            ]);
        }

        $page = $this->in->getUInt('p');
        if (!$page) {
            $page = 1;
        }

        $results     = $resultHelper->getArticlesForPage($page);
        $resultCache = $resultHelper->getResultCache();

        $totalResults = count($resultHelper->getArticleIds());
        $numPages     = ceil($totalResults / 50);
        $showingTo    = min(($page) * 50, $totalResults);

        $displayFields = $this->person->getPref('agent.ui.kb-filter-display-fields.0');
        if (!$displayFields) {
            $displayFields = ['author', 'date_created'];
        }

        $tpl = 'AgentBundle:Kb:filter.html.twig';
        if (@$_REQUEST['_partial']) {
            $tpl = 'AgentBundle:Kb:filter-page.html.twig';
        }

        $brands = $this->em->getRepository(Brand::class)->findAll();

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
                FROM article_category2usergroup
                WHERE category_id = ?
            ', [$category->getId()]);

            $articleCategories = $this->getFilteredCategory($category->getBrand()->getId());

            $catStructureData = $articleCategories;
            $catStructureData = Arrays::removeButKey($catStructureData, ['id', 'title', 'children'], true, true);
            $catStructureData = Arrays::multiRenameKey($catStructureData, 'title', 'label');
            $catStructureData = Arrays::assocToNumericArray($catStructureData, 'children');
        } else {
            $articleCategories = [];
        }

        $perms = [
            'can_edit'   => $this->person->PermissionsManager->PublishChecker->canEdit($category),
            'can_delete' => $this->person->PermissionsManager->PublishChecker->canDelete($category),
        ];

        return $this->render($tpl, [
            'results'        => $results,
            'result_id'      => $resultCache['id'],
            'display_fields' => $displayFields,
            'comment_counts' => $commentCounts,

            'is_trans_view' => $isTransView,
            'trans_lang_id' => $transLangId,

            'total_results' => $totalResults,
            'num_pages'     => $numPages,
            'cur_page'      => $page,
            'showing_to'    => $showingTo,

            'search_form'        => ['terms' => $resultCache['criteria']['terms']],
            'cache'              => $resultCache,
            'terms_summary'      => $resultCache['extra']['summary'],
            'category'           => $category,
            'cat_usergroups'     => $catUserGroups,
            'cat_structure_data' => $catStructureData,

            'article_categories' => $articleCategories,
            'brands'             => $brands,
            'perms'              => $perms,
        ]);
    }

    public function articleInfoAction($article_id)
    {
        $article = $this->em->find(Article::class, $article_id);

        $data = [
            'article_id' => $article['id'],
            'permalink'  => $this->get('object_router')->getPortalUrl($article),
            'content'    => $article->getContentHtml(),
            'is_html'    => true,
        ];

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // Compare revisions
    //###########################################################################

    public function compareRevisionsAction($rev_old_id, $rev_new_id)
    {
        $diffInfo = ContentRevisionUtil::compareRevisions(ArticleRevision::class, $rev_old_id, $rev_new_id);

        return $this->render('AgentBundle:Kb:compare-revs.html.twig', [
            'rendered_content_diff' => $diffInfo['rendered_content_diff'],
            'rendered_title_diff'   => $diffInfo['rendered_title_diff'],
        ]);
    }

    //###########################################################################
    // New article
    //###########################################################################

    public function newArticleAction()
    {
        $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');

        $articleCategories = $this->getFilteredCategory($brandId);

        if (count($articleCategories) === 0) {
            $brands = $this->em->getRepository(Brand::class)->findAll();
            $brand  = array_shift($brands);
            while (count($articleCategories) === 0 && $brand->getId()) {
                $brandId           = $brand->getId();
                $articleCategories = $this->getFilteredCategory($brandId);
                $brand             = array_shift($brands);
            }
        }

        $state = $this->em->getRepository(PersonPref::class)->getPrefForPersonId('agent.ui.state.newarticle', $this->person->id);

        $brands = $this->em->getRepository(Brand::class)->findAll();

        return $this->render('AgentBundle:Kb:newarticle.html.twig', [
            'article_categories' => $articleCategories,
            'state'              => $state,
            'brands'             => $brands,
            'selected_brand_id'  => $brandId,
        ]);
    }

    public function newArticleSaveAction(Request $request)
    {
        $newArticle = new \Application\AgentBundle\Form\Model\NewArticle($this->person);

        $formType = new \Application\AgentBundle\Form\Type\NewArticle();
        $form     = $this->get('form.factory')->create($formType, $newArticle);

        $this->db->executeUpdate("DELETE FROM people_prefs WHERE name = 'agent.ui.state.newarticle' AND person_id = ?", [$this->person->id]);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $form->isValid();

            $validator = new \Application\AgentBundle\Validator\NewArticleValidator();
            if (!$validator->isValid($newArticle)) {
                return $this->createJsonResponse([
                    'error'       => true,
                    'error_codes' => $validator->getErrorGroups(),
                ]);
            }

            $newArticle->save();

            $article = $newArticle->getArticle();

            if ($this->in->getUInt('pending_article_id')) {
                $pending_article = $this->em->find(ArticlePendingCreate::class, $this->in->getUInt('pending_article_id'));
                if ($pending_article) {
                    $this->em->remove($pending_article);
                    $this->em->flush();
                }
            }

            $rev = ContentRevisionUtil::findOrCreate($article, ['title', 'content'], $this->person);
            if ($rev) {
                $this->em->persist($rev);
                $this->em->flush();
            }

            $this->em->getRepository(PersonPref::class)->deletePrefForPersonId('agent.ui.state.newarticle', $this->person->id);

            return $this->createJsonResponse([
                'success'    => true,
                'article_id' => $article['id'],
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
        $unFilteredCategories = $this->em->getRepository(ArticleCategory::class)->getInHierarchy();

        $articleCategories = [];

        foreach ($unFilteredCategories as $c) {
            if ($brandId == $c['brand_id']) {
                $articleCategories[] = $c;
            }
        }

        return $articleCategories;
    }
}
