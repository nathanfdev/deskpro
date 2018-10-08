<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsAttachment;
use Application\DeskPRO\Entity\Person;

class NewNews
{
    /** @var string */
    public $title;

    /** @var int */
    public $category_id;

    /** @var string */
    public $status;

    /** @var string */
    public $content = '';

    /** @var string */
    public $slug;

    /** @var string */
    public $labels_json;

    /** @var array */
    public $labels = [];

    /** @var array */
    public $attach = [];

    /** @var array */
    public $blob_inline_ids = [];

    /** @var News */
    protected $_news;

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

        $news         = new News();
        $news->person = $this->_person_context;
        $news->title  = $this->title;

        $news->content = $this->_person_context->hasPerm('agent_publish.can_insert_html')
            ? App::$container->getInputCleaner()->clean($this->content ?: '', 'string', ['noclean' => true])
            : App::$container->getInputCleaner()->clean($this->content ?: '', 'html');

        $news->setStatusCode($this->status);

        if ($news->getStatusCode() == 'published' && !$this->_person_context->hasPerm('agent_publish.validate')) {
            $news->setStatusCode('hidden.unpublished');
        }

        $cat            = $this->_em->find('DeskPRO:NewsCategory', $this->category_id);
        $news->category = $cat;

        $this->_em->persist($news);
        $this->_em->flush();

        if ($this->labels) {
            $news->getLabelManager()->setLabelsArray($this->labels, $this->_em);
            $this->_em->flush();
        }

        // Message Attachments
        if ($this->attach) {
            $attachBlobs = App::getOrm()->getRepository(Blob::class)->findBy(['id' => $this->attach]);
            foreach ($attachBlobs as $blob) {
                $attach = new NewsAttachment();
                $attach->setPerson($this->_person_context)->setBlob($blob->setIsTemp(false));
                $this->_em->persist($attach);
                $this->_em->persist($blob);
                $news->addAttachment($attach);
            }
        }

        // Message Attachments
        if ($this->blob_inline_ids) {
            $inlineBlobs = App::getOrm()->getRepository(Blob::class)->findBy(['id' => $this->blob_inline_ids]);
            foreach ($inlineBlobs as $blob) {
                $this->_em->persist($blob->setIsTemp(false));
            }
        }

        $this->_em->flush();
        $this->_em->commit();

        $this->_news = $news;
    }

    public function getNews()
    {
        return $this->_news;
    }
}
