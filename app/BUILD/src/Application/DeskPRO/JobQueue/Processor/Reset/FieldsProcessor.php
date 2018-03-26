<?php

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
