<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;

/**
 * Gets the URL to the page for a resource given a context.
 */
class ObjectUrlGenerator
{
    const CONTEXT_AGENT = 'agent';
    const CONTEXT_USER  = 'user';

    /** @var \Symfony\Component\Routing\Generator\UrlGenerator */
    protected $generator;

    public function __construct(BaseUrlGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generateObjectUrl($object, array $params = [], $context = null)
    {
        if ($object instanceof \Application\DeskPRO\Entity\Article) {
            if ($context == 'agent') {
                $params['article_id'] = $object['id'];

                return $this->generator->generate('agent_kb_article', $params);
            }

            return $object->getUrlSlug();
        } elseif ($object instanceof \Application\DeskPRO\Entity\Download) {
            if ($context == 'agent') {
                $params['download_id'] = $object['id'];

                return $this->generator->generate('agent_downloads_view', $params);
            }

            return $object->getUrlSlug();
        } elseif ($object instanceof \Application\DeskPRO\Entity\Feedback) {
            if ($context == 'agent') {
                $params['feedback_id'] = $object['id'];

                return $this->generator->generate('agent_feedback_view', $params);
            }

            return $object->getUrlSlug();
        } elseif ($object instanceof \Application\DeskPRO\Entity\News) {
            if ($context == 'agent') {
                $params['news_id'] = $object['id'];

                return $this->generator->generate('agent_news_view', $params);
            }

            return $object->getUrlSlug();
        } elseif ($object instanceof \Application\DeskPRO\Entity\Person) {
            if ($context == 'agent') {
                $params['person_id'] = $object['id'];

                return $this->generator->generate('agent_people_view', $params);
            }
        } elseif ($object instanceof \Application\DeskPRO\Entity\Ticket) {
            if ($context == 'agent') {
                $params['ticket_id'] = $object['id'];

                return $this->generator->generate('agent_ticket_view', $params);
            }
        }

        return;
    }
}
