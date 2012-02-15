<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage InstallBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\InstallBundle\Data;

use Doctrine\ORM\EntityManager;

class GenerateSchema
{
	/**
	 * @var array
	 */
	protected $creates;

	/**
	 * @var array
	 */
	protected $alters;

	/**
	 * @var array
	 */
	protected $triggers;

	/**
	 * @var string
	 */
	protected $php_file;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * @return array
	 */
	public function getCreates()
	{
		$this->load();
		return $this->creates;
	}


	/**
	 * @return array
	 */
	public function getAlters()
	{
		$this->load();
		return $this->alters;
	}


	/**
	 * @return array
	 */
	public function getTriggers()
	{
		$this->load();
		return $this->triggers;
	}


	/**
	 * @return string
	 */
	public function getPhpFile()
	{
		$this->load();
		return $this->php_file;
	}


	/**
	 * Loads the schema
	 */
	protected function load()
	{
		if ($this->creates !== null) {
			return;
		}

		#------------------------------
		# Load SQL
		#------------------------------

		$em = $this->em;
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);
		$all_sql = $tool->getCreateSchemaSql($metadata);

		#------------------------------
		# Non-entity tables
		#------------------------------

		$all_sql[] = <<<SQL
CREATE TABLE `content_search` (
  `object_type` varchar(15) NOT NULL DEFAULT '',
  `object_id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  PRIMARY KEY (`object_type`,`object_id`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM
SQL;

		$all_sql[] = <<<SQL
CREATE TABLE `tickets_search_active` (
  `id` int(11) NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `priority_id` int(11) DEFAULT NULL,
  `workflow_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `person_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agent_team_id` int(11) DEFAULT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `email_gateway_id` int(11) DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `urgency` int(11) NOT NULL,
  `is_hold` tinyint(1) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_first_agent_reply` datetime DEFAULT NULL,
  `date_last_agent_reply` datetime DEFAULT NULL,
  `date_last_user_reply` datetime DEFAULT NULL,
  `date_agent_waiting` datetime DEFAULT NULL,
  `date_user_waiting` datetime DEFAULT NULL,
  `total_user_waiting` int(11) NOT NULL,
  `total_to_first_reply` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `person_id` (`person_id`) USING BTREE
) ENGINE=MyISAM
SQL;

		$all_sql[] = <<<SQL
CREATE TABLE `tickets_search_subject` (
  `ticket_id` int(11) NOT NULL,
  `subject` varchar(1000) NOT NULL,
  PRIMARY KEY (`ticket_id`)
) ENGINE=MyISAM
SQL;

		$all_sql[] = <<<SQL
CREATE TABLE `tickets_search_message` (
  `ticket_id` int(11) NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `priority_id` int(11) DEFAULT NULL,
  `workflow_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `person_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agent_team_id` int(11) DEFAULT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `email_gateway_id` int(11) DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `urgency` int(11) NOT NULL,
  `is_hold` tinyint(1) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_first_agent_reply` datetime DEFAULT NULL,
  `date_last_agent_reply` datetime DEFAULT NULL,
  `date_last_user_reply` datetime DEFAULT NULL,
  `date_agent_waiting` datetime DEFAULT NULL,
  `date_user_waiting` datetime DEFAULT NULL,
  `total_user_waiting` int(11) NOT NULL,
  `total_to_first_reply` int(11) NOT NULL,
  `content` longtext NOT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `status` (`status`),
  KEY `person_id` (`person_id`) USING BTREE,
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM
SQL;

		$all_sql[] = <<<SQL
CREATE TABLE `tickets_search_message_active` (
  `ticket_id` int(11) NOT NULL,
  `language_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `priority_id` int(11) DEFAULT NULL,
  `workflow_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `person_id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `agent_team_id` int(11) DEFAULT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `email_gateway_id` int(11) DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `urgency` int(11) NOT NULL,
  `is_hold` tinyint(1) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_first_agent_reply` datetime DEFAULT NULL,
  `date_last_agent_reply` datetime DEFAULT NULL,
  `date_last_user_reply` datetime DEFAULT NULL,
  `date_agent_waiting` datetime DEFAULT NULL,
  `date_user_waiting` datetime DEFAULT NULL,
  `total_user_waiting` int(11) NOT NULL,
  `total_to_first_reply` int(11) NOT NULL,
  `content` longtext NOT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `status` (`status`),
  KEY `person_id` (`person_id`) USING BTREE,
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM
SQL;

		#------------------------------
		# Triggers
		#------------------------------

		$all_sql[] = <<<SQL
CREATE TRIGGER ticket_search_insert AFTER INSERT ON tickets
FOR EACH ROW
BEGIN
	IF NEW.status = 'awaiting_agent' OR NEW.status = 'awaiting_user' THEN
		REPLACE INTO tickets_search_active
		SET
			id                       = NEW.id,
			language_id              = NEW.language_id,
			department_id            = NEW.department_id,
			category_id              = NEW.category_id,
			priority_id              = NEW.priority_id,
			workflow_id              = NEW.workflow_id,
			product_id               = NEW.product_id,
			person_id                = NEW.person_id,
			agent_id                 = NEW.agent_id,
			agent_team_id            = NEW.agent_team_id,
			organization_id          = NEW.organization_id,
			email_gateway_id         = NEW.email_gateway_id,
			status                   = NEW.status,
			urgency                  = NEW.urgency,
			is_hold                  = NEW.is_hold,
			date_created             = NEW.date_created,
			date_first_agent_reply   = NEW.date_first_agent_reply,
			date_last_agent_reply    = NEW.date_last_agent_reply,
			date_last_user_reply     = NEW.date_last_user_reply,
			date_agent_waiting       = NEW.date_agent_waiting,
			date_user_waiting        = NEW.date_user_waiting,
			total_user_waiting       = NEW.total_user_waiting,
			total_to_first_reply     = NEW.total_to_first_reply;

		REPLACE INTO tickets_search_subject
		SET
			ticket_id = NEW.id,
			subject   = NEW.subject;

		REPLACE INTO tickets_search_message_active
		SET
			ticket_id                = NEW.id,
			category_id              = NEW.category_id,
			priority_id              = NEW.priority_id,
			workflow_id              = NEW.workflow_id,
			product_id               = NEW.product_id,
			person_id                = NEW.person_id,
			agent_id                 = NEW.agent_id,
			agent_team_id            = NEW.agent_team_id,
			organization_id          = NEW.organization_id,
			email_gateway_id         = NEW.email_gateway_id,
			status                   = NEW.status,
			urgency                  = NEW.urgency,
			is_hold                  = NEW.is_hold,
			date_created             = NEW.date_created,
			date_first_agent_reply   = NEW.date_first_agent_reply,
			date_last_agent_reply    = NEW.date_last_agent_reply,
			date_last_user_reply     = NEW.date_last_user_reply,
			date_agent_waiting       = NEW.date_agent_waiting,
			date_user_waiting        = NEW.date_user_waiting,
			total_user_waiting       = NEW.total_user_waiting,
			total_to_first_reply     = NEW.total_to_first_reply,
			content                  = '';
	END IF;

	REPLACE INTO tickets_search_message
		SET
			ticket_id                = NEW.id,
			category_id              = NEW.category_id,
			priority_id              = NEW.priority_id,
			workflow_id              = NEW.workflow_id,
			product_id               = NEW.product_id,
			person_id                = NEW.person_id,
			agent_id                 = NEW.agent_id,
			agent_team_id            = NEW.agent_team_id,
			organization_id          = NEW.organization_id,
			email_gateway_id         = NEW.email_gateway_id,
			status                   = NEW.status,
			urgency                  = NEW.urgency,
			is_hold                  = NEW.is_hold,
			date_created             = NEW.date_created,
			date_first_agent_reply   = NEW.date_first_agent_reply,
			date_last_agent_reply    = NEW.date_last_agent_reply,
			date_last_user_reply     = NEW.date_last_user_reply,
			date_agent_waiting       = NEW.date_agent_waiting,
			date_user_waiting        = NEW.date_user_waiting,
			total_user_waiting       = NEW.total_user_waiting,
			total_to_first_reply     = NEW.total_to_first_reply,
			content                  = '';
END;
SQL;

		$all_sql[] = <<< SQL
CREATE TRIGGER ticket_search_update AFTER UPDATE ON tickets
FOR EACH ROW
BEGIN
	DECLARE allmsg LONGTEXT;

	IF NEW.status != 'awaiting_agent' AND NEW.status != 'awaiting_user' THEN
		DELETE FROM tickets_search_active WHERE id = NEW.id;
		DELETE FROM tickets_search_message_active WHERE ticket_id = NEW.id;
	ELSEIF OLD.status != 'awaiting_agent' AND OLD.status != 'awaiting_user' THEN
		REPLACE INTO tickets_search_active
		SET
			id                       = NEW.id,
			category_id              = NEW.category_id,
			priority_id              = NEW.priority_id,
			workflow_id              = NEW.workflow_id,
			product_id               = NEW.product_id,
			person_id                = NEW.person_id,
			agent_id                 = NEW.agent_id,
			agent_team_id            = NEW.agent_team_id,
			organization_id          = NEW.organization_id,
			email_gateway_id         = NEW.email_gateway_id,
			status                   = NEW.status,
			urgency                  = NEW.urgency,
			is_hold                  = NEW.is_hold,
			date_created             = NEW.date_created,
			date_first_agent_reply   = NEW.date_first_agent_reply,
			date_last_agent_reply    = NEW.date_last_agent_reply,
			date_last_user_reply     = NEW.date_last_user_reply,
			date_agent_waiting       = NEW.date_agent_waiting,
			date_user_waiting        = NEW.date_user_waiting,
			total_user_waiting       = NEW.total_user_waiting,
			total_to_first_reply     = NEW.total_to_first_reply;

		SELECT group_concat(message) INTO allmsg FROM tickets_messages WHERE ticket_id = NEW.id;
		IF allmsg IS NOT NULL THEN
			REPLACE INTO tickets_search_message_active
			SET
				ticket_id                = NEW.id,
				category_id              = NEW.category_id,
				priority_id              = NEW.priority_id,
				workflow_id              = NEW.workflow_id,
				product_id               = NEW.product_id,
				person_id                = NEW.person_id,
				agent_id                 = NEW.agent_id,
				agent_team_id            = NEW.agent_team_id,
				organization_id          = NEW.organization_id,
				email_gateway_id         = NEW.email_gateway_id,
				status                   = NEW.status,
				urgency                  = NEW.urgency,
				is_hold                  = NEW.is_hold,
				date_created             = NEW.date_created,
				date_first_agent_reply   = NEW.date_first_agent_reply,
				date_last_agent_reply    = NEW.date_last_agent_reply,
				date_last_user_reply     = NEW.date_last_user_reply,
				date_agent_waiting       = NEW.date_agent_waiting,
				date_user_waiting        = NEW.date_user_waiting,
				total_user_waiting       = NEW.total_user_waiting,
				total_to_first_reply     = NEW.total_to_first_reply,
				content                  = allmsg;
		END IF;
	ELSE
		UPDATE tickets_search_active
		SET
			id                       = NEW.id,
			category_id              = NEW.category_id,
			priority_id              = NEW.priority_id,
			workflow_id              = NEW.workflow_id,
			product_id               = NEW.product_id,
			person_id                = NEW.person_id,
			agent_id                 = NEW.agent_id,
			agent_team_id            = NEW.agent_team_id,
			organization_id          = NEW.organization_id,
			email_gateway_id         = NEW.email_gateway_id,
			status                   = NEW.status,
			urgency                  = NEW.urgency,
			is_hold                  = NEW.is_hold,
			date_created             = NEW.date_created,
			date_first_agent_reply   = NEW.date_first_agent_reply,
			date_last_agent_reply    = NEW.date_last_agent_reply,
			date_last_user_reply     = NEW.date_last_user_reply,
			date_agent_waiting       = NEW.date_agent_waiting,
			date_user_waiting        = NEW.date_user_waiting,
			total_user_waiting       = NEW.total_user_waiting,
			total_to_first_reply     = NEW.total_to_first_reply
		WHERE id = NEW.id;

		UPDATE tickets_search_message
		SET
			category_id              = NEW.category_id,
			priority_id              = NEW.priority_id,
			workflow_id              = NEW.workflow_id,
			product_id               = NEW.product_id,
			person_id                = NEW.person_id,
			agent_id                 = NEW.agent_id,
			agent_team_id            = NEW.agent_team_id,
			organization_id          = NEW.organization_id,
			email_gateway_id         = NEW.email_gateway_id,
			status                   = NEW.status,
			urgency                  = NEW.urgency,
			is_hold                  = NEW.is_hold,
			date_created             = NEW.date_created,
			date_first_agent_reply   = NEW.date_first_agent_reply,
			date_last_agent_reply    = NEW.date_last_agent_reply,
			date_last_user_reply     = NEW.date_last_user_reply,
			date_agent_waiting       = NEW.date_agent_waiting,
			date_user_waiting        = NEW.date_user_waiting,
			total_user_waiting       = NEW.total_user_waiting,
			total_to_first_reply     = NEW.total_to_first_reply
		WHERE ticket_id = NEW.id;
	END IF;

	UPDATE tickets_search_message_active
	SET
		category_id              = NEW.category_id,
		priority_id              = NEW.priority_id,
		workflow_id              = NEW.workflow_id,
		product_id               = NEW.product_id,
		person_id                = NEW.person_id,
		agent_id                 = NEW.agent_id,
		agent_team_id            = NEW.agent_team_id,
		organization_id          = NEW.organization_id,
		email_gateway_id         = NEW.email_gateway_id,
		status                   = NEW.status,
		urgency                  = NEW.urgency,
		is_hold                  = NEW.is_hold,
		date_created             = NEW.date_created,
		date_first_agent_reply   = NEW.date_first_agent_reply,
		date_last_agent_reply    = NEW.date_last_agent_reply,
		date_last_user_reply     = NEW.date_last_user_reply,
		date_agent_waiting       = NEW.date_agent_waiting,
		date_user_waiting        = NEW.date_user_waiting,
		total_user_waiting       = NEW.total_user_waiting,
		total_to_first_reply     = NEW.total_to_first_reply
	WHERE ticket_id = NEW.id;

	IF NEW.subject != OLD.subject THEN
		UPDATE tickets_search_subject
		SET subject = NEW.subject
		WHERE ticket_id = NEW.id;
	END IF;
END;
SQL;

		$all_sql[] = <<<SQL
CREATE TRIGGER ticket_search_delete AFTER DELETE ON tickets
FOR EACH ROW
BEGIN
	DELETE FROM tickets_search_active WHERE id = OLD.id;
	DELETE FROM tickets_search_message WHERE ticket_id = OLD.id;
	DELETE FROM tickets_search_message_active WHERE ticket_id = OLD.id;
	DELETE FROM tickets_search_subject WHERE ticket_id = OLD.id;
END;
SQL;

		$all_sql[] = <<<SQL
CREATE TRIGGER ticket_search_message_insert AFTER INSERT ON tickets_messages
FOR EACH ROW
BEGIN
	DECLARE allmsg LONGTEXT;
	SELECT group_concat(message) INTO allmsg FROM tickets_messages WHERE ticket_id = NEW.ticket_id;
	IF allmsg IS NOT NULL THEN
		UPDATE tickets_search_message
		SET content = allmsg
		WHERE ticket_id = NEW.ticket_id;

		UPDATE tickets_search_message_active
		SET content = allmsg
		WHERE ticket_id = NEW.ticket_id;
	END IF;
END;
SQL;

		#------------------------------
		# Organise it
		#------------------------------

		$xa = 0;
		$xc = 0;
		$xt = 0;

		$php_creates  = array();
		$php_alters   = array();
		$php_triggers = array();

		foreach ($all_sql as $s) {
			$s = trim($s);

			if (preg_match('#^CREATE TRIGGER#', $s)) {
				$s_ex = var_export($s, true);

				$this->triggers[] = $s;
				$php_triggers[] = "\$queries['trigger'][$xt] = $s_ex;";
				$xt++;
			} elseif (preg_match('#^ALTER#', $s)) {
				$s = str_replace(array("\r\n", "\n"), ' ', $s);
				$s_ex = var_export($s, true);

				$this->alters[] = $s;
				$php_alters[] = "\$queries['alter'][$xa] = $s_ex;";
				$xa++;
			} else {
				$s = str_replace(array("\r\n", "\n"), ' ', $s);
				$s .= ' DEFAULT CHARSET=utf8';

				$s_ex = var_export($s, true);

				$this->creates[] = $s;
				$php_creates[] = "\$queries['create'][$xc] = $s_ex;";
				$xc++;
			}
		}
		$php = "<?php\n\n\$queries = array('create' => array(), 'alter' => array(), 'trigger' => array());\n\n";
		$php .= implode("\n", $php_creates);
		$php .= "\n\n\n\n\n";
		$php .= implode("\n", $php_alters);
		$php .= "\n\n\n\n\n";
		$php .= implode("\n", $php_triggers);
		$php .= "\n\n\n\n\nreturn \$queries;\n";

		$this->php_file = $php;
	}
}
