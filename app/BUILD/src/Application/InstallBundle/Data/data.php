<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

$em->getConnection()->executeUpdate(
    "
INSERT INTO `ticket_priorities` (`id`, `title`, `priority`)
VALUES
	(1, 'Priority 1', 10),
	(2, 'Priority 2', 20),
	(3, 'Priority 3', 30);
INSERT INTO `ticket_categories` (`id`, `parent_id`, `title`, `display_order`)
VALUES
	(1, NULL, 'Category 1', 10),
	(2, NULL, 'Category 2', 20),
	(3, NULL, 'Category 3', 30);
INSERT INTO `ticket_workflows` (`id`, `title`, `display_order`)
VALUES
	(4, 'Workflow 1', 10),
	(5, 'Workflow 2', 20),
	(6, 'Workflow 3', 30);
"
);

// TEMPORARY CHAT DATA

$sql = <<<'SQL'
  INSERT INTO `custom_data_chat` (`id`, `conversation_id`, `field_id`, `root_field_id`, `value`, `input`)
VALUES
  (1, 3, 1, 1, 0, 'this is a custom chat text answer!'),
  (2, 3, 2, 2, 1, '');
SQL;

$em->getConnection()->executeUpdate(
    $sql
);
