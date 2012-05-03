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
 * @subpackage InstallBundle
 */

namespace Application\InstallBundle\Data;

use Doctrine\ORM\EntityManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;

class DataInitializer
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected $container;

	/**
	 * @var bool
	 */
	protected $is_import = false;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $admin_user;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}

	public function setImportMode()
	{
		$this->is_import = true;
	}

	public function getAdminUser()
	{
		if ($this->admin_user) {
			return $this->admin_user;
		}

		$this->admin_user = $this->container->getEm()
				->createQuery("SELECT p FROM DeskPRO:Person p WHERE p.is_agent = true AND p.can_admin = true ORDER BY p.id DESC")
				->setMaxResults(1)
				->getOneOrNullResult();

		return $this->admin_user;
	}

	public function run()
	{
		$this->runSearchIndex();
		$this->runInitPerms();
		$this->runInitAdminNotifications();
		$this->runInitInitialData();
	}

	public function runInitPerms()
	{
		// During import the default cats arent created
		if (!$this->is_import) {
			// By default everyone can see the default categories
			$this->container->getDb()->insert('article_category2usergroup', array('category_id' => 1, 'usergroup_id' => 1));
			$this->container->getDb()->insert('news_category2usergroup', array('category_id' => 1, 'usergroup_id' => 1));
			$this->container->getDb()->insert('download_category2usergroup', array('category_id' => 1, 'usergroup_id' => 1));
			$this->container->getDb()->insert('feedback_category2usergroup', array('category_id' => 1, 'usergroup_id' => 1));

			// Initial agent has access to all deps
			$this->container->getDb()->insert('department_permissions', array('department_id' => 1, 'person_id' => 1, 'app' => 'tickets'));
			$this->container->getDb()->insert('department_permissions', array('department_id' => 1, 'person_id' => 1, 'app' => 'chat'));
			$this->container->getDb()->insert('department_permissions', array('department_id' => 2, 'person_id' => 1, 'app' => 'tickets'));
			$this->container->getDb()->insert('department_permissions', array('department_id' => 2, 'person_id' => 1, 'app' => 'chat'));

			// The everyone group has access to all deps too
			$this->container->getDb()->insert('department_permissions', array('department_id' => 1, 'usergroup_id' => 1, 'app' => 'tickets'));
			$this->container->getDb()->insert('department_permissions', array('department_id' => 1, 'usergroup_id' => 1, 'app' => 'chat'));
			$this->container->getDb()->insert('department_permissions', array('department_id' => 2, 'usergroup_id' => 1, 'app' => 'tickets'));
			$this->container->getDb()->insert('department_permissions', array('department_id' => 2, 'usergroup_id' => 1, 'app' => 'chat'));
		}
	}

	public function runSearchIndex()
	{
		$types = array(
			array('article',   'articles',  'DeskPRO:Article'),
			array('download',  'downloads', 'DeskPRO:Download'),
			array('feedback',  'feedback',  'DeskPRO:Feedback'),
			array('news',      'news',      'DeskPRO:News'),
		);

		foreach ($types as $t) {
			list ($content_type, $table, $entity) = $t;
			$all_ids = $this->container->getDb()->fetchAllCol("SELECT id FROM $table ORDER BY id ASC");
			$batch = $this->container->getEm()->getRepository($entity)->getByIds($all_ids);
			if ($batch) {
				$this->container->getSearchAdapter()->updateObjectsInIndex($batch);
			}
		}
	}

	public function runInitAdminNotifications()
	{
		$agent = \Application\DeskPRO\App::getOrm()->createQuery("SELECT p FROM DeskPRO:Person p WHERE p.can_admin = 1 ORDER BY p.id ASC")
			->setMaxResults(1)
			->getOneOrNullResult();

		// Possible to be in import mode and no admin
		if (!$agent) {
			return;
		}

		for ($i = 1; $i <= 5; $i++) {
			$this->container->getDb()->insert('ticket_filter_subscriptions', array(
				'filter_id' => $i,
				'person_id' => $agent->id,
				'email_new' => 1,
				'email_user_activity' => 1,
				'email_agent_activity' => 1,
				'email_property_change' => 1,
				'alert_new' => 1,
				'alert_user_activity' => 1,
				'alert_agent_activity' => 1,
				'alert_property_change' => 1,
			));
		}

		$prefs = array();
		$prefs['chat_message.email'] = 1;
		$prefs['login_attempt_fail.email'] = 1;
		$prefs['new_feedback.email'] = 1;
		$prefs['new_feedback_validate.email'] = 1;
		$prefs['new_comment.email'] = 1;
		$prefs['new_comment_validate.email'] = 1;

		foreach ($prefs as $p => $v) {
			$this->container->getDb()->insert('people_prefs', array(
				'person_id' => $agent->id,
				'name' => 'agent_notif.' . $p,
				'value_str' => $v,
				'value_array' => 'N;',
			));
		}
	}

	public function runInitInitialData()
	{
		#------------------------------
		# Example ticket
		#------------------------------

		$department = $this->container->getEm()
				->createQuery("SELECT d FROM DeskPRO:Department d ORDER BY d.id DESC")
				->setMaxResults(1)
				->getOneOrNullResult();

		$user = Person::newContactPerson(array(
			'name' => 'DeskPRO Support',
			'email' => 'support@deskpro.com',
			'is_confirmed' => true,
		));
		$user->getPrimaryEmail()->is_validated = true;

		$this->container->getEm()->persist($user);

		$ticket = new Ticket();
		$ticket->creation_system = Ticket::CREATED_WEB_PERSON;
		$ticket->person          = $user;
		$ticket->agent           = $this->getAdminUser();
		$ticket->department      = $department;
		$ticket->subject         = 'Welcome to DeskPRO';
		$ticket->status          = Ticket::STATUS_AWAITING_AGENT;

		$this->container->getEm()->persist($ticket);

		$message = new TicketMessage();
		$message->person  = $user;
		$message->ticket  = $ticket;
		$message->message = <<<STR
Welcome to DeskPRO!<br /><br />

This is a sample ticket that demonstrates how the system will look when a user submits a new ticket. Feel free to reply, close or delete this whenever you want.<br /><br />

If you run into any problems or have any questions, you can always visit our helpdesk at <a href="http://support.deskpro.com/">support.deskpro.com</a>.<br /><br />

Best Regards,<br /><br />

The DeskPRO Team
STR;
		$ticket->addMessage($message);

		$this->container->getEm()->persist($message);

		$this->container->getEm()->flush();

		// Make sure its in ticket active
	}
}
