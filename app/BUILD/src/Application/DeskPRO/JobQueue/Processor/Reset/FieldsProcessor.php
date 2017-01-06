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

namespace Application\DeskPRO\JobQueue\Processor\Reset;

use Application\DeskPRO\Monolog\NullLogger;
use Application\InstallBundle\Data\DefaultData\TicketLayoutData;

class FieldsProcessor extends Base
{
    const JOB_TYPE = 'reset.fields';

    /**
     * {@inheritdoc}
     */
    protected function doProcess(array $data)
    {
        $types = ['article', 'chat', 'feedback', 'organizations', 'ticket'];
        foreach ($types as $type) {
            $this->connection->executeUpdate("DELETE FROM custom_data_$type");
            $this->connection->executeUpdate("DELETE FROM custom_def_$type");
        }

        $this->connection->executeUpdate('DELETE FROM custom_data_person');
        $this->connection->executeUpdate('DELETE FROM custom_def_people');

        $this->connection->executeUpdate('DELETE FROM custom_data_product');
        $this->connection->executeUpdate('DELETE FROM custom_def_products');

        $this->connection->executeUpdate('DELETE FROM custom_field_data');
        $this->connection->executeUpdate('DELETE FROM custom_field_definition');

        $this->connection->executeUpdate('DELETE FROM products');
        $this->connection->executeUpdate('DELETE FROM ticket_categories');
        $this->connection->executeUpdate('DELETE FROM ticket_workflows');
        $this->connection->executeUpdate('DELETE FROM ticket_priorities');

        $sh = $this->container->getSettingsHandler();
        $sh->setSetting('core.use_product', false);
        $sh->setSetting('core.use_ticket_priority', false);
        $sh->setSetting('core.use_ticket_workflow', false);
        $sh->setSetting('core.use_ticket_category', false);

        $layout_data = new TicketLayoutData($this->container, new NullLogger());
        $layout_data->runReset();
    }
}
