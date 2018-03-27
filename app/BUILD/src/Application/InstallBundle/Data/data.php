<?php

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
