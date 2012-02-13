<?php

namespace Application\ReportBundle\Stat\DeskPRO;

use Application\ReportBundle\Stat\Base\AbstractStat;
use Application\ReportBundle\Stat\Base\QueryBuilder;
use Application\ReportBundle\Stat\Searcher\TicketSearch;

/**
 * Represent Abstract Stats for Tickets
 */
abstract class AbstractTicket extends AbstractStat
{
        public function init()
        {
                // Set the searcher to use, we want the ticket searcher
		$this->setSearcher(new TicketSearch());

                $this->addAvailableGroups(array(
                        // Simple groupings - all on ticket table
			'tickets.department_id'	        => array('label' => 'Department', 'limit' => false),
			'tickets.category_id'	        => array('label' => 'Category', 'limit' => false),
			'tickets.priority_id'	        => array('label' => 'Proprity', 'limit' => false),
			'tickets.workflow_id'	        => array('label' => 'Workflow', 'limit' => false),
                        'tickets.product_id'            => array('label' => 'Product', 'limit' => false),
			'tickets.language_id'	        => array('label' => 'Language', 'limit' => false),
			'tickets.agent_id'              => array('label' => 'Agent', 'limit' => false),
			'tickets.agent_team_id'	        => array('label' => 'Agent Team', 'limit' => false),
                        'tickets.date_created'          => array('label' => 'Hour Created', 'limit' => false, 'format' => "DATE_FORMAT(tickets.date_created, '%H')"),
                        'tickets.organization_id'       => array('label' => 'Organization', 'limit' => 20),
                        'tickets.person_id'             => array('label' => 'Person', 'limit' => false),

                        // Advanced groupings
                        'labels_tickets.label'                  => array('label' => 'Label', 'limit' => 20),
                        'person2usergroups.usergroup_id'        => array('label' => 'User Group', 'limit' => 20),
		));
        }

        /**
         * Overriden from parent.
         *
         * Need to do additional checking to see if group by fields require
         * table joins
         *
         * @param QueryBuilder $query
         */
        protected function applyGroupByToQuery(QueryBuilder $query)
	{
                parent::applyGroupByToQuery($query);

                // Some group by fields require joins to other tables, or other conditions

                foreach ($this->grouping as $groupField) {

			switch ($groupField) {
                                case 'tickets.product_id':
                                        // Only get the top level products
                                        $query->leftJoin('tickets', 'products', 'products', 'tickets.product_id = products.id')
                                              ->andWhere('products.parent_id IS NULL');
                                        break;
                                case 'tickets.language_id':
                                        // Only get the top level languages
                                        $query->leftJoin('tickets', 'languages', 'languages', 'tickets.language_id = languages.id')
                                              ->andWhere('languages.parent_id IS NULL');
                                        break;
                                // These fields require joining to additional tables
                                case 'labels_tickets.label':
                                        $query->leftJoin('tickets', 'labels_tickets', 'labels_tickets', 'tickets.id = labels_tickets.ticket_id');
                                        break;
                                case 'person2usergroups.usergroup_id':
                                        $query->leftJoin('tickets', 'person2usergroups', 'person2usergroups', 'tickets.person_id = person2usergroups.usergroup_id');
                                        break;
                        }
		}
	}

}