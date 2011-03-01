##BEGIN:trigger.ticket_search_insert##
CREATE TRIGGER ticket_search_insert AFTER INSERT ON tickets
FOR EACH ROW
BEGIN
	IF NEW.status = 'open' OR NEW.status = 'pending' THEN
		INSERT INTO tickets_search_active
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
			status                   = NEW.status,
			urgency                  = NEW.urgency,
			date_created             = NEW.date_created,
			date_first_agent_reply   = NEW.date_first_agent_reply,
			date_last_agent_reply    = NEW.date_last_agent_reply,
			date_last_user_reply     = NEW.date_last_user_reply,
			date_agent_waiting       = NEW.date_agent_waiting,
			date_user_waiting        = NEW.date_user_waiting,
			total_user_waiting       = NEW.total_user_waiting,
			total_to_first_reply     = NEW.total_to_first_reply;

		INSERT INTO tickets_search_subject
		SET
			ticket_id = NEW.id,
			subject   = NEW.subject;

		INSERT INTO tickets_search_message_active
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
			status                   = NEW.status,
			urgency                  = NEW.urgency,
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

	INSERT INTO tickets_search_message
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
			status                   = NEW.status,
			urgency                  = NEW.urgency,
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


##BEGIN:trigger.ticket_search_update##
CREATE TRIGGER ticket_search_update AFTER UPDATE ON tickets
FOR EACH ROW
BEGIN
	DECLARE allmsg LONGTEXT;

	IF NEW.status != 'open' AND NEW.status != 'pending' THEN
		DELETE FROM tickets_search_active WHERE id = NEW.id;
		DELETE FROM tickets_search_message_active WHERE ticket_id = NEW.id;
	ELSEIF OLD.status != 'open' AND OLD.status != 'pending' THEN
		INSERT INTO tickets_search_active
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
			status                   = NEW.status,
			urgency                  = NEW.urgency,
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
			INSERT INTO tickets_search_message_active
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
				status                   = NEW.status,
				urgency                  = NEW.urgency,
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
			status                   = NEW.status,
			urgency                  = NEW.urgency,
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
			status                   = NEW.status,
			urgency                  = NEW.urgency,
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
		status                   = NEW.status,
		urgency                  = NEW.urgency,
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


##BEGIN:trigger.ticket_search_delete##
CREATE TRIGGER ticket_search_delete AFTER DELETE ON tickets
FOR EACH ROW
BEGIN
	DELETE FROM tickets_search_active WHERE id = OLD.id;
	DELETE FROM tickets_search_message WHERE ticket_id = OLD.id;
	DELETE FROM tickets_search_message_active WHERE ticket_id = OLD.id;
	DELETE FROM tickets_search_subject WHERE ticket_id = OLD.id;
END;

##BEGIN:trigger.ticket_search_message_insert##
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