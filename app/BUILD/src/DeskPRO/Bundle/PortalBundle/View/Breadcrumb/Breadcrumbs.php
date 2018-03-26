<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\Breadcrumb;

class Breadcrumbs implements \IteratorAggregate, \Countable
{
    const PORTAL          = 'portal';
    const PROFILE         = 'profile';
    const PROFILE_EMAILS  = 'profile_emails';
    const REGISTER        = 'register';
    const PASSWORD_RESET  = 'password.reset';
    const PASSWORD_SET    = 'password.set';
    const LOGIN           = 'login';
    const KB              = 'kb';
    const KB_CAT          = 'kb.cat';
    const KB_VIEW         = 'kb.view';
    const NEWS            = 'news';
    const NEWS_CAT        = 'news.cat';
    const NEWS_VIEW       = 'news.view';
    const DOWNLOADS       = 'downloads';
    const DOWNLOADS_CAT   = 'downloads.cat';
    const DOWNLOADS_VIEW  = 'downloads.view';
    const TOPICS          = 'guides';
    const GUIDE           = 'guides.guide';
    const TOPIC           = 'guides.view';
    const SEARCH          = 'search';
    const FEEDBACK        = 'feedback';
    const FEEDBACK_FILTER = 'feedback.filter';
    const FEEDBACK_VIEW   = 'feedback.view';
    const TICKETS         = 'tickets';
    const TICKETS_VIEW    = 'tickets.view';
    const TICKETS_NEW     = 'tickets.new';
    const CHAT            = 'chat';
    const CHAT_VIEW       = 'chat.view';

    /**
     * @var array
     */
    private $breadcrumbs;

    /**
     * @param string $url
     * @param array  $route_params
     * @param string $type
     * @param mixed  $var
     *
     * @return $this
     */
    public function add($url, $type, $var = null)
    {
        $this->breadcrumbs[] = [
            'url'    => $url,
            'type'   => $type,
            'phrase' => is_array($var) && isset($var['phrase']) ? $var['phrase'] : null,
            'var'    => $var,
        ];

        return $this;
    }

    /**
     * @param string $url
     * @param array  $route_params
     * @param string $type
     * @param mixed  $var
     *
     * @return $this
     */
    public function prepend($url, $type, $var = null)
    {
        array_unshift($this->breadcrumbs, [
            'url'    => $url,
            'type'   => $type,
            'phrase' => is_array($var) && isset($var['phrase']) ? $var['phrase'] : null,
            'var'    => $var,
        ]);

        return $this;
    }

    /**
     * Clears all items.
     */
    public function clear()
    {
        $this->breadcrumbs = [];
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->breadcrumbs;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->breadcrumbs);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->breadcrumbs);
    }
}
