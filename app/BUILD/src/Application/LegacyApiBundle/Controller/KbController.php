<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Rating;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\Searcher\ArticleSearch;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Doctrine\ExplicitIdPersister;
use Orb\Util\Numbers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @ApiModes("all")
 */
class KbController extends AbstractController
{
    public function searchAction()
    {
        $search_map = [
            'category_id'          => ArticleSearch::TERM_CATEGORY,
            'category_id_specific' => ArticleSearch::TERM_CATEGORY_SPECIFIC,
            'label'                => ArticleSearch::TERM_LABEL,
            'new'                  => ArticleSearch::TERM_NEW,
            'popular'              => ArticleSearch::TERM_POPULAR,
            'status'               => ArticleSearch::TERM_STATUS,
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
            $terms[] = ['type' => ArticleSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
                'date2' => $date_created_end,
            ]];
        } elseif ($date_created_start) {
            $terms[] = ['type' => ArticleSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => [
                'date1' => $date_created_start,
            ]];
        }

        $order_by = $this->in->getString('order');
        if (!$order_by) {
            $order_by = 'date:desc';
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('article', $terms, $extra, $this->in->getUint('cache_id'), new ArticleSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $articles = App::getEntityRepository(Article::class)->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($ids),
            'cache_id' => $result_cache->id,
            'articles' => $this->getApiData($articles),
        ]);
    }

    /**
     * @param Request $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function newArticleAction(Request $request)
    {
        $errors  = [];
        $article = new Article();

        $set_title    = null;
        $set_content  = null;
        $title_lang   = [];
        $content_lang = [];

        if (isset($_POST['title']) && isset($_POST['content']) && is_array($_POST['title']) && is_array($_POST['content'])) {
            foreach ($this->container->getLanguageData()->getAll() as $lang) {
                $lang_id = $lang->getId();

                $title       = $this->in->getString("title.$lang_id");
                $content_val = (string) $this->in->getRaw("content.$lang_id");

                if ($lang_id == $article->language->getId()) {
                    $set_title   = $title;
                    $set_content = $content_val;
                    continue;
                }

                if (!$title && !$content_val) {
                    continue;
                }

                $title_lang[$lang->getId()]   = $title;
                $content_lang[$lang->getId()] = $content_val;
            }
        } else {
            $set_title   = $this->in->getString('title');
            $set_content = (string) $this->in->getRaw('content');
        }

        if ($set_title) {
            $article->title = $set_title;
        } else {
            $errors['title'] = ['required_field.title', 'title is required'];
        }

        if ($set_content) {
            $article->content = $set_content;
        } else {
            $errors['content'] = ['required_field.content', 'content is required'];
        }

        $status = $this->in->getString('status');
        if (!$status) {
            $status = 'published';
        }
        $article->setStatusCode($status);

        $date = $this->in->getUint('date');
        if ($date) {
            $article->date_created = new \DateTime('@'.$date);
            if ($status == 'published') {
                $article->date_published = new \DateTime('@'.$date);
            }
        }

        if ($this->in->checkIsset('date_published') && $status != 'published') {
            $date_published = $this->in->getUint('date_published');
            if ($date_published) {
                $article->date_published = new \DateTime('@'.$date_published);
            }
        }

        $date_end = $this->in->getUint('date_end');
        if ($date_end) {
            $article->date_end   = new \DateTime('@'.$date_end);
            $article->end_action = $this->in->getString('end_action') ?: Article::END_ACTION_DELETE;
        }

        $cat_ids = $this->in->getCleanValueArray('category_id', 'uint', 'discard');
        $cats    = $this->em->getRepository(ArticleCategory::class)->getByIds($cat_ids);
        if (!$cats) {
            $errors['category_id'] = ['invalid_argument.category_id', 'no categories found'];
        }
        $article->setCategories($cats);

        $product_ids = $this->in->getCleanValueArray('product_id', 'uint', 'discard');
        $products    = $this->em->getRepository(Product::class)->getByIds($product_ids);
        if ($products) {
            $article->setProducts($products);
        }

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        $this->_insertArticleAttachments($article);
        $article->person = $this->person;

        if ($explicitId = $request->request->get('with_entity_id')) {
            ExplicitIdPersister::persistWithId(
                $this->em,
                $article,
                $explicitId,
                function ($entity) {
                    $this->em->persist($entity);
                    $this->em->flush();
                }
            );
        } else {
            $this->em->persist($article);
            $this->em->flush();
        }

        $field_manager      = $this->container->getSystemService('article_fields_manager');
        $post_custom_fields = $this->getCustomFieldInput();
        if (!empty($post_custom_fields)) {
            $field_manager->saveFormToObject($post_custom_fields, $article, true);
        }

        $labels = $this->in->getCleanValueArray('label', 'string', 'discard');
        if ($labels) {
            $article->getLabelManager()->setLabelsArray($labels, $this->em);
        }

        $this->em->flush();

        // Set other langs
        foreach ($title_lang as $lang_id => $title) {
            $lang        = $this->container->getLanguageData()->get($lang_id);
            $content_val = $content_lang[$lang_id];

            $rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'title', $title);
            $this->em->persist($rec);

            $rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'content', $content_val);
            $this->em->persist($rec);
        }

        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $article->id],
            $this->generateUrl(
                'api_kb_article',
                ['article_id' => $article->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $article_id
     *
     * @return Response
     */
    public function getArticleAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id);

        return $this->createApiResponse(['article' => $article->toApiData()]);
    }

    /**
     * @param int $article_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function postArticleAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id, 'edit');

        $lang_id = $this->in->getUint('language_id');
        $lang    = null;
        if ($lang_id) {
            $lang = $this->container->getLanguageData()->get($lang_id);
        }
        if ($lang) {
            $article->language = $lang;
        }

        $revs = [];

        if (is_array($_POST['title']) && is_array($_POST['content'])) {
            $set_title   = null;
            $set_content = null;

            foreach ($this->container->getLanguageData()->getAll() as $lang) {
                $lang_id = $lang->getId();

                $title       = $this->in->getString("title.$lang_id");
                $content_val = (string) $this->in->getRaw("content.$lang_id");

                if ($lang_id == $article->language->getId()) {
                    $set_title   = $title;
                    $set_content = $content_val;
                    continue;
                }

                if (!$title && !$content_val) {
                    continue;
                }

                $rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'title', $title);
                $this->em->persist($rec);

                $rec = $this->container->getObjectLangRepository()->setRec($lang, $article, 'content', $content_val);
                $this->em->persist($rec);
            }
        } else {
            $set_title   = $this->in->getString('title');
            $set_content = (string) $this->in->getRaw('content');
        }

        if ($set_title && $set_title != $article->title) {
            $article->title = $set_title;

            $rev        = ContentRevisionUtil::findOrCreate($article, 'title', $this->person);
            $rev->title = $article->title;

            $revs['title'] = $rev;
        }

        if ($set_content && $set_content != $article->content) {
            $article->content = $set_content;

            $rev          = ContentRevisionUtil::findOrCreate($article, ['content'], $this->person);
            $rev->content = $article->content;

            $revs['content'] = $rev;
        }

        $status = $this->in->getString('status');
        if ($status) {
            $article->setStatusCode($status);
        }

        $cat_ids = $this->in->getCleanValueArray('category_id', 'uint', 'discard');
        $cats    = $this->em->getRepository(ArticleCategory::class)->getByIds($cat_ids);
        if ($cats) {
            $article->setCategories($cats);
        }

        $product_ids = $this->in->getCleanValueArray('product_id', 'uint', 'discard');
        $products    = $this->em->getRepository(Product::class)->getByIds($product_ids);
        if ($products) {
            $article->setProducts($products);
        } elseif ($this->in->getBool('remove_product')) {
            $article->setProducts([]);
        }

        if ($this->in->checkIsset('date_published') && $article->status != 'published') {
            $date_published = $this->in->getUint('date_published');
            if ($date_published) {
                $article->date_published = new \DateTime('@'.$date_published);
            } else {
                $article->date_published = null;
            }
        }

        if ($this->in->checkIsset('date_end')) {
            $date_end = $this->in->getUint('date_end');
            if ($date_end) {
                $article->date_end   = new \DateTime('@'.$date_end);
                $article->end_action = $this->in->getString('end_action') ?: Article::END_ACTION_DELETE;
            } else {
                $article->date_end   = null;
                $article->end_action = null;
            }
        }

        $this->_insertArticleAttachments($article);

        foreach ($revs as $rev) {
            $this->em->persist($rev);
        }
        $this->em->persist($article);

        $field_manager      = $this->container->getSystemService('article_fields_manager');
        $post_custom_fields = $this->getCustomFieldInput();
        if (!empty($post_custom_fields)) {
            $field_manager->saveFormToObject($post_custom_fields, $article, true);
        }

        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param Article $article
     */
    protected function _insertArticleAttachments(Article $article)
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
                $this->_addArticleAttachment($blob, $article);
            }
        }

        foreach ($this->in->getCleanValueArray('attach_id') as $blob_id) {
            $this->_addArticleAttachment($blob_id, $article);
        }
    }

    /**
     * @param int     $blob_id
     * @param Article $article
     *
     * @return \Application\DeskPRO\Entity\ArticleAttachment|bool
     */
    protected function _addArticleAttachment($blob_id, Article $article)
    {
        if ($blob_id instanceof \Application\DeskPRO\Entity\Blob) {
            $blob = $blob_id;
        } else {
            $blob = $this->em->getRepository(Blob::class)->find($blob_id);
        }

        if ($blob) {
            $attach           = new \Application\DeskPRO\Entity\ArticleAttachment();
            $attach['blob']   = $blob;
            $attach['person'] = $this->person;

            $article->addAttachment($attach);

            return $attach;
        } else {
            return false;
        }
    }

    /**
     * @param int $article_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function deleteArticleAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id, 'delete');

        $article->status_code = 'hidden.deleted';
        $this->em->persist($article);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $article_id
     *
     * @return Response
     */
    public function getArticleVotesAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id);
        $votes   = App::getEntityRepository(Rating::class)->getRatingsFor('article', $article->id);

        return $this->createApiResponse(['votes' => $this->getApiData($votes)]);
    }

    /**
     * @param int $article_id
     *
     * @return Response
     */
    public function getArticleCommentsAction($article_id)
    {
        $article  = $this->_getArticleOr404($article_id);
        $comments = $this->em->getRepository(ArticleComment::class)->getComments($article);

        return $this->createApiResponse(['comments' => $this->getApiData($comments)]);
    }

    /**
     * @param int $article_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function newArticleCommentAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id);

        $content = $this->in->getString('content');
        if (!$content) {
            return $this->createApiErrorResponse('required_field.content', 'Missing content');
        }

        $person_id = $this->in->getUint('person_id');
        $person    = null;
        if ($person_id) {
            $person = $this->em->getRepository(Person::class)->find($person_id);
        }

        $status = $this->in->getString('status');

        $comment                 = new ArticleComment();
        $comment->article        = $article;
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
                'api_kb_article_comments_comment',
                ['article_id' => $article->id, 'comment_id' => $comment->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $article_id
     * @param int $comment_id
     *
     * @return Response
     */
    public function getArticleCommentAction($article_id, $comment_id)
    {
        $article = $this->_getArticleOr404($article_id);
        $comment = $this->em->getRepository(ArticleComment::class)->find($comment_id);
        if (!$comment || $comment->article->id != $article->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return $this->createApiResponse(['comment' => $comment->toApiData()]);
    }

    /**
     * @param int $article_id
     * @param int $comment_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function postArticleCommentAction($article_id, $comment_id)
    {
        $article = $this->_getArticleOr404($article_id);
        $comment = $this->em->getRepository(ArticleComment::class)->find($comment_id);
        if (!$comment || $comment->article->id != $article->id) {
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
     * @param int $article_id
     * @param int $comment_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function deleteArticleCommentAction($article_id, $comment_id)
    {
        $article = $this->_getArticleOr404($article_id);
        $comment = $this->em->getRepository(ArticleComment::class)->find($comment_id);
        if (!$comment || $comment->article->id != $article->id) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $this->em->remove($comment);
        $this->em->flush();

        $this->_sendCommentDeletedNotification($comment);

        return $this->createSuccessResponse();
    }

    /**
     * @param int $article_id
     *
     * @return Response
     */
    public function getArticleAttachmentsAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id);

        return $this->createApiResponse(['attachments' => $this->getApiData($article->attachments)]);
    }

    /**
     * @param int $article_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function newArticleAttachmentAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id, 'edit');

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
            $blob    = $this->em->find(Blob::class, $blob_id);
            if (!$blob) {
                return $this->createApiErrorResponse('invalid_argument.attach_id', 'attach_id not found');
            }
        }

        $attach = $this->_addArticleAttachment($blob, $article);

        $this->em->persist($article);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['id' => $attach->id],
            $this->generateUrl(
                'api_kb_article_attachment',
                ['article_id' => $article->id, 'attachment_id' => $attach->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $article_id
     * @param int $attachment_id
     *
     * @return Response
     */
    public function getArticleAttachmentAction($article_id, $attachment_id)
    {
        $article = $this->_getArticleOr404($article_id);
        $exists  = false;
        foreach ($article->attachments as $attachment) {
            if ($attachment->id == $attachment_id) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(['exists' => $exists]);
    }

    /**
     * @param int $article_id
     * @param int $attachment_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function deleteArticleAttachmentAction($article_id, $attachment_id)
    {
        $article = $this->_getArticleOr404($article_id);
        foreach ($article->attachments as $k => $attachment) {
            if ($attachment->id == $attachment_id) {
                $article->attachments->remove($k);
                $this->em->remove($attachment);
                break;
            }
        }

        $this->em->persist($article);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $article_id
     *
     * @return Response
     */
    public function getArticleLabelsAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id);

        return $this->createApiResponse(['labels' => $this->getApiData($article->labels)]);
    }

    /**
     * @param int $article_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return Response
     */
    public function postArticleLabelsAction($article_id)
    {
        $article = $this->_getArticleOr404($article_id, 'edit');
        $label   = $this->in->getString('label');

        if ($label === '') {
            return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
        }

        $article->getLabelManager()->addLabel($label);
        $this->em->persist($article);
        $this->em->flush();

        return $this->createApiCreateResponse(
            ['label' => $label],
            $this->generateUrl(
                'api_kb_article_label',
                ['article_id' => $article->id, 'label' => $label],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int    $article_id
     * @param string $label
     *
     * @return Response
     */
    public function getArticleLabelAction($article_id, $label)
    {
        $article = $this->_getArticleOr404($article_id);

        if ($article->getLabelManager()->hasLabel($label)) {
            return $this->createApiResponse(['exists' => true]);
        } else {
            return $this->createApiResponse(['exists' => false]);
        }
    }

    /**
     * @param int    $article_id
     * @param string $label
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function deleteArticleLabelAction($article_id, $label)
    {
        $article = $this->_getArticleOr404($article_id, 'edit');

        $article->getLabelManager()->removeLabel($label);
        $this->em->persist($article);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @return Response
     */
    public function getValidatingCommentsAction()
    {
        $comments   = $this->em->getRepository(ArticleComment::class)->getValidatingComments();
        $entity_key = 'article';
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
     * @return Response
     */
    public function getCategoriesAction()
    {
        $categories = $this->em->getRepository(ArticleCategory::class)->getFlatHierarchy();

        return $this->createApiResponse(['categories' => $categories]);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @return Response
     */
    public function postCategoriesAction()
    {
        $errors = [];

        $title = $this->in->getString('title');
        if (!$title) {
            $errors['title'] = ['required_field.title', 'title empty or missing'];
        }

        $category = new \Application\DeskPRO\Entity\ArticleCategory();

        $category->title = $title;

        $parent_id = $this->in->getUint('parent_id');
        if ($parent_id) {
            $parent = $this->em->getRepository(ArticleCategory::class)->find($parent_id);
            if ($parent) {
                $category->setParent($parent);
            }
        }

        $category->display_order = $this->in->getUint('display_order');

        if ($errors) {
            return $this->createApiMultipleErrorResponse($errors);
        }

        if ($this->in->checkIsset('usergroup_id')) {
            $usergroup_ids = $this->in->getCleanValueArray('usergroup_id', 'uint');
        } else {
            $usergroup_ids = [$this->container->getUserGroups()->getEveryoneGroup()->getId()];
        }

        $this->db->beginTransaction();

        try {
            $this->em->persist($category);
            $this->em->flush();

            foreach ($usergroup_ids as $usergroup_id) {
                if (!$usergroup_id) {
                    continue;
                }
                App::getDb()->insert('article_category2usergroup', [
                    'category_id'  => $category->getId(),
                    'usergroup_id' => $usergroup_id,
                ]);
            }

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $this->createApiCreateResponse(
            ['id' => $category->id],
            $this->generateUrl(
                'api_kb_category',
                ['category_id' => $category->id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $category_id
     *
     * @return Response
     */
    public function getCategoryAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        return $this->createApiResponse(['category' => $category->toApiData()]);
    }

    /**
     * @param int $category_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function postCategoryAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $errors = [];

        if ($this->in->checkIsset('title')) {
            $title = $this->in->getString('title');
            if (!$title) {
                $errors['title'] = ['required_field.title', 'title empty or missing'];
            }
            $category->title = $title;
        }

        if ($this->in->checkIsset('parent_id')) {
            $parent_id = $this->in->getUint('parent_id');
            if ($parent_id) {
                $parent = $this->em->getRepository(ArticleCategory::class)->find($parent_id);
                if ($parent) {
                    $category->setParent($parent);
                }
            } else {
                $category->setParent(null);
            }
        }

        if ($this->in->checkIsset('display_order')) {
            $category->display_order = $this->in->getUint('display_order');
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $category_id
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function deleteCategoryAction($category_id)
    {
        $this->_getCategoryOr404($category_id);

        try {
            \Application\DeskPRO\Publish\CategoryEdit::deleteCategory('articles', $category_id);
        } catch (\OutOfBoundsException $e) {
            return $this->createApiErrorResponse('invalid_argument.category_id', 'category is not empty');
        }

        return $this->createSuccessResponse();
    }

    /**
     * @param int $category_id
     *
     * @return Response
     */
    public function getCategoryArticlesAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $terms = [
            ['type' => ArticleSearch::TERM_CATEGORY_SPECIFIC, 'op' => 'contains', 'options' => [$category->id]],
        ];

        $order_by = $this->in->getString('order');
        if (!$order_by) {
            $order_by = 'date:desc';
        }

        $extra = [];
        if ($order_by !== null) {
            $extra['order_by'] = $order_by;
        }

        $result_cache = $this->getApiSearchResult('article', $terms, $extra, $this->in->getUint('cache_id'), new ArticleSearch());

        $page = $this->in->getUint('page');
        if (!$page) {
            $page = 1;
        }

        $per_page = Numbers::bound($this->in->getUint('per_page') ?: 25, 1, 250);

        $ids = $result_cache->results;

        $page_ids = \Orb\Util\Arrays::getPageChunk($ids, $page, $per_page);
        $articles = App::getEntityRepository(Article::class)->getByIds($page_ids, true);

        return $this->createApiResponse([
            'page'     => $page,
            'per_page' => $per_page,
            'total'    => count($ids),
            'cache_id' => $result_cache->id,
            'articles' => $this->getApiData($articles),
        ]);
    }

    /**
     * @param int $category_id
     *
     * @return Response
     */
    public function getCategoryGroupsAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        return $this->createApiResponse(['groups' => $this->getApiData($category->usergroups)]);
    }

    /**
     * @param int $category_id
     *
     * @return Response
     */
    public function postCategoryGroupsAction($category_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $group_id = $this->in->getUint('id');

        $group = $this->em->getRepository(Usergroup::class)->find($group_id);
        if (!$group || $group->is_agent_group) {
            return $this->createApiErrorResponse('invalid_argument.id', 'group cannot be found or is not available');
        }

        $exists = false;
        foreach ($category->usergroups as $group) {
            if ($group->id == $group_id) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $this->db->insert('article_category2usergroup', [
                'category_id'  => $category->id,
                'usergroup_id' => $group_id,
            ]);
        }

        return $this->createApiCreateResponse(
            ['id' => $group_id],
            $this->generateUrl(
                'api_kb_category_group',
                ['category_id' => $category->id, 'group_id' => $group_id],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );
    }

    /**
     * @param int $category_id
     * @param int $group_id
     *
     * @return Response
     */
    public function getCategoryGroupAction($category_id, $group_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        $exists = false;
        foreach ($category->usergroups as $group) {
            if ($group->id == $group_id) {
                $exists = true;
                break;
            }
        }

        return $this->createApiResponse(['exists' => $exists]);
    }

    /**
     * @param int $category_id
     * @param int $group_id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Application\LegacyApiBundle\HttpFoundation\JsonResponse|Response
     */
    public function deleteCategoryGroupAction($category_id, $group_id)
    {
        $category = $this->_getCategoryOr404($category_id);

        foreach ($category->usergroups as $key => $group) {
            if ($group->id == $group_id) {
                $category->usergroups->remove($key);
                $this->em->persist($category);
                $this->em->flush();
                break;
            }
        }

        return $this->createSuccessResponse();
    }

    /**
     * @return Response
     */
    public function getFieldsAction()
    {
        $field_manager = $this->container->getSystemService('article_fields_manager');
        $fields        = $field_manager->getFields();

        return $this->createApiResponse(['fields' => $this->getApiData($fields)]);
    }

    /**
     * @return Response
     */
    public function getProductsAction()
    {
        $products = $this->em->getRepository(Product::class)->getFlatHierarchy();

        return $this->createApiResponse(['products' => $products]);
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\Article
     */
    protected function _getArticleOr404($id, $check_perm = false)
    {
        $article = $this->em->getRepository(Article::class)->findOneById($id);

        if (!$article) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no article with ID $id");
        }

        if ($check_perm) {
            if ($check_perm == 'edit' && !$this->person->PermissionsManager->PublishChecker->canEdit($article)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }

            if ($check_perm == 'delete' && !$this->person->PermissionsManager->PublishChecker->canDelete($article)) {
                throw new AccessDeniedHttpException('Sorry, you do not have permission to perform this action');
            }
        }

        return $article;
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\ArticleCategory
     */
    protected function _getCategoryOr404($id)
    {
        $category = $this->em->getRepository(ArticleCategory::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException("There is no category with ID $id");
        }

        return $category;
    }
}
