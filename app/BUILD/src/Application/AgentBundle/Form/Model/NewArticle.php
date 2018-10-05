<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleAttachment;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Component\Util\StringUtils;

class NewArticle
{
    /** @var string */
    public $title;

    /** @var int */
    public $category_id;

    /** @var string */
    public $status;

    /** @var string */
    public $content;

    /** @var int */
    public $language_id;

    /** @var string */
    public $slug;

    /** @var array */
    public $labels = [];

    /** @var array */
    public $attach = [];

    /** @var array */
    public $blob_inline_ids = [];

    /** @var Article */
    protected $_article;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct(Person $person_context)
    {
        $this->_person_context = $person_context;

        $this->_em = App::getOrm();
    }

    public function save()
    {
        $this->_em->beginTransaction();

        $article         = new Article();
        $article->person = $this->_person_context;
        $article->setStatusCode($this->status);

        if ($article->getStatusCode() == 'published' && !$this->_person_context->hasPerm('agent_publish.validate')) {
            $article->setStatusCode('hidden.unpublished');
        }

        $article->title = $this->title;

        $article->content = $this->_person_context->hasPerm('agent_publish.can_insert_html')
            ? App::$container->getInputCleaner()->clean($this->content ?: '', 'string', ['noclean' => true])
            : App::$container->getInputCleaner()->clean($this->content ?: '', 'html');

        $lang = null;
        if ($this->language_id) {
            $lang = App::getContainer()->getLanguageData()->get($this->language_id);
        }
        if (!$lang) {
            $lang = App::getContainer()->getLanguageData()->getDefault();
        }
        $article->language = $lang;

        $cat = $this->_em->find('DeskPRO:ArticleCategory', $this->category_id);
        $article->addToCategory($cat);

        $this->_em->persist($article);
        $this->_em->flush();

        if ($this->labels) {
            $article->getLabelManager()->setLabelsArray($this->labels, $this->_em);
        }

        // Message Attachments
        foreach ($this->attach as $blob_id) {
            $blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);
            if ($blob) {
                $attach = new ArticleAttachment();
                $attach->setPerson($this->_person_context)->setBlob($blob->setIsTemp(false));
                $this->_em->persist($attach);
                $this->_em->persist($blob);
                $article->addAttachment($attach);
            }
        }

        // Message Attachments
        foreach ($this->blob_inline_ids as $blob_id) {
            /** @var Blob|null $blob */
            $blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);
            if ($blob && StringUtils::ensureAttachment($blob, $article->getContentHtml())) {
                $this->_em->persist($blob->setIsTemp(false));
            }
        }

        $this->_em->flush();
        $this->_em->commit();
        $this->_article = $article;
    }

    public function getArticle()
    {
        return $this->_article;
    }
}
