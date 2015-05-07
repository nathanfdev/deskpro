<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\View\Breadcrumb;

class Breadcrumbs implements \IteratorAggregate, \Countable
{
    const PORTAL           = 'portal';
    const PROFILE          = 'profile';
    const KB               = 'kb';
    const KB_CAT           = 'kb.cat';
    const KB_VIEW          = 'kb.view';
    const NEWS             = 'news';
    const NEWS_CAT         = 'news.cat';
    const NEWS_VIEW        = 'news.view';
    const DOWNLOADS        = 'downloads';
    const DOWNLOADS_CAT    = 'downloads.cat';
    const DOWNLOADS_VIEW   = 'downloads.view';
    const FEEDBACK         = 'feedback';
    const FEEDBACK_FILTER  = 'feedback.filter';
    const FEEDBACK_VIEW    = 'feedback.view';
    const TICKETS          = 'tickets';
    const TICKETS_VIEW     = 'tickets.view';
    const CHAT             = 'chat';
    const CHAT_VIEW        = 'chat.view';

    /**
     * @var array
     */
    private $breadcrumbs;

    /**
     * @param string  $route
     * @param array   $route_params
     * @param string  $type
     * @param mixed   $var
     * @return $this
     */
    public function add($route, array $route_params = null, $type, $var = null)
    {
        $this->breadcrumbs[] = array(
            'route'        => $route,
            'route_params' => $route_params ?: array(),
            'type'         => $type,
            'phrase'       => is_array($var) && isset($var['phrase']) ? $var['phrase'] : null,
            'var'          => $var,
        );

        return $this;
    }

    /**
     * @param string  $route
     * @param array   $route_params
     * @param string  $type
     * @param mixed   $var
     * @return $this
     */
    public function prepend($route, array $route_params = null, $type, $var = null)
    {
        array_unshift($this->breadcrumbs, array(
            'route'        => $route,
            'route_params' => $route_params ?: array(),
            'type'         => $type,
            'phrase'       => is_array($var) && isset($var['phrase']) ? $var['phrase'] : null,
            'var'          => $var,
        ));

        return $this;
    }

    /**
     * Clears all items
     */
    public function clear()
    {
        $this->breadcrumbs = array();
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