<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Orb\Util\DpStrings;

/**
 * @property int $id
 * @property FacebookApp $app
 * @property string $graph_id
 * @property string $page_token
 * @property string $user_token
 * @property string $name
 * @property string $verify_token
 * @property string $picture_url
 * @property string $user_graph_id
 * @property bool $import_wall_posts
 * @property bool $disable_own_wall_posts
 * @property bool $import_direct_messages
 * @property bool $is_enabled
 * @property bool $is_connected
 * @property bool $is_tested
 * @property \DateTime date_user_token_received
 * @property \DateTime date_created
 */
class FacebookPage extends DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * @var FacebookApp
     */
    protected $app;

    /**
     * @var string the facebook graph id for this page
     */
    protected $graph_id;

    /**
     * @var string the facebook page token we use
     */
    protected $page_token;

    /**
     * @var string the facebook user token we use
     */
    protected $user_token;

    /**
     * @var string the facebook user that set this page up
     */
    protected $user_graph_id;

    /**
     * @var string the page name
     */
    protected $name;

    /**
     * @var string the verify token
     */
    protected $verify_token;

    /**
     * @var string the page name
     */
    protected $picture_url;

    /**
     * @var bool true if we turn wall posts into tickets
     */
    protected $import_wall_posts;

    /**
     * @var bool true if we ignore initial posts on wall made by the page itself
     */
    protected $disable_own_wall_posts;

    /**
     * @var bool true if we make new tickets from direct messages
     */
    protected $import_direct_messages;

    /**
     * @var bool if the acount is enabled or not
     */
    protected $is_enabled;

    /**
     * @var bool the account succeeded in connecting to the API with current credentials
     */
    protected $is_connected;

    /**
     * @var bool if the account was tested via SMS with the current credentials
     */
    protected $is_tested;

    /**
     * @var \DateTime the expires date of user token
     */
    protected $date_user_token_received;

    /**
     * @var \DateTime date created page on deskpro
     */
    protected $date_created;

    public function __construct()
    {
        $this->date_created           = new \DateTime();
        $this->verify_token           = DpStrings::random(8);
        $this->import_wall_posts      = false;
        $this->disable_own_wall_posts = false;
        $this->import_direct_messages = false;
        $this->is_enabled             = false;
        $this->is_connected           = false;
        $this->is_tested              = false;
        $this->user_token_expires     = null;
        $this->page_token_expires     = null;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data        = parent::toApiData($primary, $deep, $visited);
        $data['app'] = $this->app->toApiData();

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder
            ->setTable('facebook_pages')
            ->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\FacebookPage')
            ->setChangeTrackingPolicyNotify()
        ;
        $builder->mapId();
        $builder->mapString('graph_id', null, null, true);
        $builder->mapString('page_token');
        $builder->mapString('user_token');
        $builder->mapString('user_graph_id');
        $builder->mapString('name');
        $builder->mapString('verify_token');
        $builder->mapString('picture_url');
        $builder->mapBoolean('import_wall_posts');
        $builder->mapBoolean('disable_own_wall_posts');
        $builder->mapBoolean('import_direct_messages');
        $builder->mapBoolean('is_enabled');
        $builder->mapBoolean('is_connected');
        $builder->mapBoolean('is_tested');
        $builder->mapDateTime('date_user_token_received');
        $builder->mapDateTime('date_created', false);

        $builder->addManyToOne('app', 'Application\DeskPRO\Entity\FacebookApp');
    }
}
