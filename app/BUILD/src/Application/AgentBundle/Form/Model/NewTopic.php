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
