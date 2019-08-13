<?php

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

use Application\DeskPRO\Entity\CustomDefBilling;
use Application\DeskPRO\Entity\CustomDefTicket;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\CustomTicketFieldDefinitionAlias;

/**
 * Class CompilerCustomDataTest.
 */
class CompilerCustomDataTest extends AbstractCompilerTest
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        // reload kernel to reset field manager cache
        $this->getApiKernel(true);

        parent::setUp();
    }

    public function test_group_by_choice_field()
    {
        $def = new CustomDefTicket();
        $def->setWidgetType(CustomDefTicket::TYPE_CHOICE);

        $choice1 = new CustomDefTicket();
        $choice1->setTitle('Choice 1');
        $choice1->setParent($def);

        $choice2 = new CustomDefTicket();
        $choice2->setTitle('Choice 2');
        $choice2->setParent($def);

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($def);
        $em->flush();

        $this->assertDpqlQuery(
            <<<DPQL
SELECT DPQL_COUNT(), tickets.custom_data[{$def->getId()}]
FROM tickets
GROUP BY tickets.custom_data[{$def->getId()}]
DPQL
            ,
            <<<SQL
SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*), IF(`tickets_custom_data_{$def->getId()}`.`value`, `tickets_custom_data_{$def->getId()}_field`.`title`, `tickets_custom_data_{$def->getId()}`.`input`), `tickets_custom_data_{$def->getId()}_field`.`id` 
FROM `tickets` 
LEFT JOIN `custom_data_ticket` AS `tickets_custom_data_{$def->getId()}` ON (`tickets_custom_data_{$def->getId()}`.`ticket_id` = `tickets`.`id` AND tickets_custom_data_{$def->getId()}.root_field_id = '{$def->getId()}')
LEFT JOIN `custom_def_ticket` AS `tickets_custom_data_{$def->getId()}_field` ON (`tickets_custom_data_{$def->getId()}`.`field_id` = `tickets_custom_data_{$def->getId()}_field`.`id`)
GROUP BY `tickets_custom_data_{$def->getId()}_field`.`id` 
ORDER BY IF(`tickets_custom_data_{$def->getId()}`.`value`, `tickets_custom_data_{$def->getId()}_field`.`title`, `tickets_custom_data_{$def->getId()}`.`input`) 
LIMIT 2500
SQL
        );
    }

    public function test_group_by_text_field()
    {
        $def = new CustomDefTicket();
        $def->setWidgetType(CustomDefTicket::TYPE_TEXT);

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($def);
        $em->flush();

        $this->assertDpqlQuery(
            <<<DPQL
SELECT DPQL_COUNT(), tickets.custom_data[{$def->getId()}]
FROM tickets
GROUP BY tickets.custom_data[{$def->getId()}]
DPQL
            ,
            <<<SQL
SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*), IF(`tickets_custom_data_{$def->getId()}`.`value`, `tickets_custom_data_{$def->getId()}_field`.`title`, `tickets_custom_data_{$def->getId()}`.`input`) 
FROM `tickets`
LEFT JOIN `custom_data_ticket` AS `tickets_custom_data_{$def->getId()}` ON (`tickets_custom_data_{$def->getId()}`.`ticket_id` = `tickets`.`id` AND tickets_custom_data_{$def->getId()}.root_field_id = '{$def->getId()}')
LEFT JOIN `custom_def_ticket` AS `tickets_custom_data_{$def->getId()}_field` ON (`tickets_custom_data_{$def->getId()}`.`field_id` = `tickets_custom_data_{$def->getId()}_field`.`id`) 
GROUP BY IF(`tickets_custom_data_{$def->getId()}`.`value`, `tickets_custom_data_{$def->getId()}_field`.`title`, `tickets_custom_data_{$def->getId()}`.`input`) 
ORDER BY IF(`tickets_custom_data_{$def->getId()}`.`value`, `tickets_custom_data_{$def->getId()}_field`.`title`, `tickets_custom_data_{$def->getId()}`.`input`) 
LIMIT 2500
SQL
        );
    }

    public function test_get_by_alias()
    {
        $alias = new CustomTicketFieldDefinitionAlias();
        $alias->setAlias('myalias');

        $def = new CustomDefTicket();
        $def->setWidgetType(CustomDefTicket::TYPE_TEXT);
        $def->addAlias($alias);

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($def);
        $em->flush();

        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT DPQL_COUNT(), tickets.custom_data[myalias]
FROM tickets
GROUP BY tickets.custom_data[myalias]
DPQL
            ,
            <<<SQL
SELECT /*+ MAX_EXECUTION_TIME(30000) */ COUNT(*), IF(`tickets_custom_data_myalias`.`value`, `tickets_custom_data_myalias_field`.`title`, `tickets_custom_data_myalias`.`input`)
FROM `tickets`
LEFT JOIN `custom_data_ticket` AS `tickets_custom_data_myalias` ON (`tickets_custom_data_myalias`.`ticket_id` = `tickets`.`id` AND tickets_custom_data_myalias.root_field_id = '{$def->getId()}')
LEFT JOIN `custom_def_ticket` AS `tickets_custom_data_myalias_field` ON (`tickets_custom_data_myalias`.`field_id` = `tickets_custom_data_myalias_field`.`id`)
GROUP BY IF(`tickets_custom_data_myalias`.`value`, `tickets_custom_data_myalias_field`.`title`, `tickets_custom_data_myalias`.`input`)
ORDER BY IF(`tickets_custom_data_myalias`.`value`, `tickets_custom_data_myalias_field`.`title`, `tickets_custom_data_myalias`.`input`)
LIMIT 2500
SQL
        );
    }

    public function test_not_empty_billing_fields()
    {
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $def = new CustomDefBilling();
        $def->setTitle('Comment');
        $def->setHandlerClass('Application\DeskPRO\CustomFields\Handler\Text');
        $def->setIsEnabled(1);

        $em->persist($def);
        $em->flush();

        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT
  DPQL_TOTAL(DPQL_TIME_LENGTH(ticket_charges.charge_time)) AS 'Time',
  DPQL_TOTAL(DPQL_FORMAT(ticket_charges.amount, 'number', 2)) AS 'Amount (${billingCurrency})', ${billingSelectBits} ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
FROM ticket_charges
WHERE ticket_charges.date_created = ${date}
ORDER BY ticket_charges.date_created
DPQL
            ,
            <<<SQL
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `ticket_charges`.`charge_time`, `ticket_charges`.`amount`, IF(`ticket_charges_custom_data_{$def->getId()}`.`value`, `ticket_charges_custom_data_{$def->getId()}_field`.`title`, `ticket_charges_custom_data_{$def->getId()}`.`input`), `ticket_charges_agent`.`id`, (CASE WHEN (LENGTH(ticket_charges_agent.first_name) > 0 AND LENGTH(ticket_charges_agent.last_name) > 0) THEN CONCAT(ticket_charges_agent.first_name, ' ', ticket_charges_agent.last_name) WHEN LENGTH(ticket_charges_agent.name) > 0 THEN ticket_charges_agent.name WHEN LENGTH(ticket_charges_agent.last_name) > 0 THEN ticket_charges_agent.last_name WHEN LENGTH(ticket_charges_agent.first_name) > 0 THEN ticket_charges_agent.first_name ELSE CONCAT('ID-', ticket_charges_agent.id) END) , `ticket_charges`.`date_created`, `ticket_charges_ticket`.`id`, `ticket_charges_ticket`.`subject`
FROM `ticket_charges`
LEFT JOIN `custom_data_billing` AS `ticket_charges_custom_data_{$def->getId()}` ON (`ticket_charges_custom_data_{$def->getId()}`.`ticket_charge_id` = `ticket_charges`.`id` AND ticket_charges_custom_data_{$def->getId()}.root_field_id = '{$def->getId()}')
LEFT JOIN `custom_def_billing` AS `ticket_charges_custom_data_{$def->getId()}_field` ON (`ticket_charges_custom_data_{$def->getId()}`.`field_id` = `ticket_charges_custom_data_{$def->getId()}_field`.`id`)
LEFT JOIN `people` AS `ticket_charges_agent` ON (`ticket_charges`.`agent_id` = `ticket_charges_agent`.`id`)
LEFT JOIN `tickets` AS `ticket_charges_ticket` ON (`ticket_charges`.`ticket_id` = `ticket_charges_ticket`.`id`)
WHERE (`ticket_charges`.`date_created` = 'date') ORDER BY `ticket_charges`.`date_created`
LIMIT 2500
SQL
        );
    }

    public function test_empty_billing_fields()
    {
        // billing fields removed in parent::setUp

        $this->assertDpqlQuery(
            <<<'DPQL'
SELECT
  DPQL_TOTAL(DPQL_TIME_LENGTH(ticket_charges.charge_time)) AS 'Time',
  DPQL_TOTAL(DPQL_FORMAT(ticket_charges.amount, 'number', 2)) AS 'Amount (${billingCurrency})', ${billingSelectBits} ticket_charges.agent, ticket_charges.date_created, ticket_charges.ticket
FROM ticket_charges
WHERE ticket_charges.date_created = ${date}
ORDER BY ticket_charges.date_created
DPQL
            ,
            <<<'SQL'
SELECT /*+ MAX_EXECUTION_TIME(30000) */ `ticket_charges`.`charge_time`, `ticket_charges`.`amount`, `ticket_charges_agent`.`id`, (CASE WHEN (LENGTH(ticket_charges_agent.first_name) > 0 AND LENGTH(ticket_charges_agent.last_name) > 0) THEN CONCAT(ticket_charges_agent.first_name, ' ', ticket_charges_agent.last_name) WHEN LENGTH(ticket_charges_agent.name) > 0 THEN ticket_charges_agent.name WHEN LENGTH(ticket_charges_agent.last_name) > 0 THEN ticket_charges_agent.last_name WHEN LENGTH(ticket_charges_agent.first_name) > 0 THEN ticket_charges_agent.first_name ELSE CONCAT('ID-', ticket_charges_agent.id) END) , `ticket_charges`.`date_created`, `ticket_charges_ticket`.`id`, `ticket_charges_ticket`.`subject`
FROM `ticket_charges`
LEFT JOIN `people` AS `ticket_charges_agent` ON (`ticket_charges`.`agent_id` = `ticket_charges_agent`.`id`)
LEFT JOIN `tickets` AS `ticket_charges_ticket` ON (`ticket_charges`.`ticket_id` = `ticket_charges_ticket`.`id`)
WHERE (`ticket_charges`.`date_created` = 'date') ORDER BY `ticket_charges`.`date_created`
LIMIT 2500
SQL
        );
    }
}
