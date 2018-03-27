<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Facebook;

use Application\DeskPRO\Entity\FacebookApp;
use Doctrine\ORM\EntityManager;

class EditApp
{
    /**
     * The unique ID.
     *
     * @var FacebookApp
     */
    protected $app;

    /**
     * @var string facebook app id
     */
    public $app_id;

    /**
     * @var string facebook app secret
     */
    public $app_secret;

    /**
     * @var string an identifier tthat we put next to the app
     */
    public $name;

    /**
     * @var string url to smaller icon image
     */
    public $icon_url;

    /**
     * @var string url to logo url
     */
    public $logo_url;

    /**
     * @param FacebookApp $app
     */
    public function __construct(FacebookApp $app)
    {
        $this->app        = $app;
        $this->app_id     = $app->app_id;
        $this->app_secret = $app->app_secret;
        $this->name       = $app->name;
        $this->icon_url   = $app->icon_url;
        $this->logo_url   = $app->logo_url;
    }

    public function save(EntityManager $em)
    {
        $app = $this->app;

        $app->app_id     = $this->app_id;
        $app->app_secret = $this->app_secret;
        $app->name       = $this->name;
        $app->icon_url   = $this->icon_url;
        $app->logo_url   = $this->logo_url;

        $em->persist($app);
        $em->flush();

        return $app;
    }
}
