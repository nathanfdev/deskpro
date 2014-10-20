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


use Application\DeskPRO\Entity\FacebookApp;
use Application\DeskPRO\ORM\EntityManager;

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
 