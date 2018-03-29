<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Guide;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Topic;

class NewTopic
{
    /** @var string */
    public $title;
    /** @var int */
    public $guide_id;
    /** @var int */
    public $parent_id;
    /** @var string */
    public $status;
    /** @var string */
    public $content = '';
    /** @var string */
    public $content_input = '';
    /** @var string */
    public $content_input_type = '';

    /** @var string */
    public $slug;
    /** @var array */
    public $attach = [];
    /** @var News */
    protected $_topic;

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

        $topic = new Topic();
        $topic->setPerson($this->_person_context);
        $topic->setTitle($this->title);

        $topic->setContent($this->_person_context->hasPerm('agent_publish.can_insert_html')
            ? App::$container->getInputCleaner()->clean($this->content ?: '', 'string', ['noclean' => true])
            : App::$container->getInputCleaner()->clean($this->content ?: '', 'html'));

        $topic->setContentInput($this->content_input);

        $topic->setContentInputType($this->content_input_type);

        $topic->setStatusCode($this->status);

        if ($topic->getStatusCode() == 'published' && !$this->_person_context->hasPerm('agent_publish.validate')) {
            $topic->setStatusCode('hidden.unpublished');
        }

        $guide = $this->_em->find(Guide::class, $this->guide_id);
        $topic->setGuide($guide);

        $parent = $this->_em->find(Topic::class, $this->parent_id);
        if ($parent) {
            $topic->setParent($parent);
        }

        $this->_em->persist($topic);
        $this->_em->flush();

        $this->_em->commit();

        $this->_topic = $topic;
    }

    public function getTopic()
    {
        return $this->_topic;
    }
}
