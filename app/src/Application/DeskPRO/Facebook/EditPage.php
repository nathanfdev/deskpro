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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Facebook
 */

namespace Application\DeskPRO\Facebook;

use Application\DeskPRO\Entity\FacebookPage;
use Application\DeskPRO\ORM\EntityManager;

class EditPage
{
    /**
     * The unique ID.
     *
     * @var FacebookPage
     */
    protected $page;

    /**
     * @var EditApp
     */
    public $app;

    /**
     * @var string the facebook graph id for this page
     */
    public $graph_id;

    /**
     * @var string the facebook page token we use
     */
    public $page_token;

    /**
     * @var string the facebook page token we use
     */
    public $user_token;

    /**
     * @var string the facebook user that set this page up
     */
    public $user_graph_id;

    /**
     * @var string the page name
     */
    public $name;

    /**
     * @var string the page name
     */
    public $picture_url;

    /**
     * @var bool true if we turn wall posts into tickets
     */
    public $import_wall_posts;

    /**
     * @var bool true if we ignore initial posts on wall made by the page itself
     */
    public $disable_own_wall_posts;

    /**
     * @var bool true if we make new tickets from direct messages
     */
    public $import_direct_messages;

    /**
     * @var bool if the acount is enabled or not
     */
    public $is_enabled;

    /**
     * @var bool the account succeeded in connecting to the API with current credentials
     */
    public $is_connected;

    /**
     * @var bool if the account was tested via SMS with the current credentials
     */
    public $is_tested;

    /**
     * @param FacebookPage $page
     */
    public function __construct(FacebookPage $page)
    {
        $this->page = $page;

        $this->graph_id = $page->graph_id;
        $this->user_graph_id = $page->user_graph_id;
        $this->page_token = $page->page_token;
        $this->user_token = $page->user_token;
        $this->name = $page->name;
        $this->picture_url = $page->picture_url;
        $this->import_wall_posts = $page->import_wall_posts ? true : false;
        $this->disable_own_wall_posts = $page->disable_own_wall_posts ? true : false;
        $this->import_direct_messages = $page->import_direct_messages ? true : false;
        $this->is_enabled = $page->is_enabled ? true : false;
        $this->is_connected = $page->is_connected ? true : false;
        $this->is_tested = $page->is_tested ? true : false;
        $this->app = new EditApp($page->app);
    }

    public function save(EntityManager $em)
    {
        $fb_app = $this->app->save($em);

        $page = $this->page;

        $page->graph_id = $this->graph_id;
        $page->user_graph_id = $this->user_graph_id;
        $page->page_token = $this->page_token;
        $page->user_token = $this->user_token;
        $page->name = $this->name;
        $page->picture_url = $this->picture_url;
        $page->import_wall_posts = $this->import_wall_posts ? true : false;
        $page->disable_own_wall_posts = $this->disable_own_wall_posts ? true : false;
        $page->import_direct_messages = $this->import_direct_messages ? true : false;
        $page->is_enabled = $this->is_enabled ? true : false;
        $page->is_connected = $this->is_connected ? true : false;
        $page->is_tested = $this->is_tested ? true : false;
        $page->app = $fb_app;

        if (!$page->id) {
            $fb = new FacebookApi($page->app);
            $fb->extendPageToken($page);
            $fb->subscribeToFeed($page);
        }

        $em->persist($page);
        $em->flush();

        return $page;
    }
}
