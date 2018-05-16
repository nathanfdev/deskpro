<?php

namespace DpTest\DeskPRO\Bundle\ReportBundle\Dpql2;

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
}
