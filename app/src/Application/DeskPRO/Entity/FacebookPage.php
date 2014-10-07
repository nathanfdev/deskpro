<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use \Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @property int $id
 * @property FacebookApp $app
 * @property string $graph_id
 * @property string $page_token
 * @property string $user_token
 * @property string $name
 * @property string $picture_url
 * @property string $user_graph_id
 * @property bool $import_wall_posts
 * @property bool $disable_own_wall_posts
 * @property bool $import_direct_messages
 * @property bool $is_enabled
 * @property bool $is_connected
 * @property bool $is_tested
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


	public function __construct()
	{
		$this->import_wall_posts = false;
		$this->disable_own_wall_posts = false;
		$this->import_direct_messages = false;
		$this->is_enabled = false;
		$this->is_connected = false;
		$this->is_tested = false;
	}


	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['app'] = $this->app->toApiData();

		return $data;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

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
		$builder->mapString('picture_url');
		$builder->mapBoolean('import_wall_posts');
		$builder->mapBoolean('disable_own_wall_posts');
		$builder->mapBoolean('import_direct_messages');
		$builder->mapBoolean('is_enabled');
		$builder->mapBoolean('is_connected');
		$builder->mapBoolean('is_tested');

		$builder->addManyToOne('app', 'Application\DeskPRO\Entity\FacebookApp');
	}
}
