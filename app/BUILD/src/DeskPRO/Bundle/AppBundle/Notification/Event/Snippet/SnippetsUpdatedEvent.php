<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Event\Snippet;

use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Notification\Event\AbstractSystemEvent;

/**
 * Class SnippetsUpdatedEvent.
 */
class SnippetsUpdatedEvent extends AbstractSystemEvent
{
    const EVENT_NAME = 'snippet.snippets_updated';

    private $snippet   = null;
    private $snippetId = null;
    private $action    = null;

    /**
     * SnippetsUpdatedEvent constructor.
     *
     * @param Snippet $snippet
     * @param string  $action
     */
    public function __construct($snippet, $action = null)
    {
        $this->snippet = $snippet;
        if ($snippet) {
            $this->snippetId = $snippet->getId();
        }
        $this->action = $action;
    }

    /**
     * @return Snippet|null
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function __sleep()
    {
        return ['snippetId', 'action'];
    }
}
