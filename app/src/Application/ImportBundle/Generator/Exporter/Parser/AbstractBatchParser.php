<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Generator\Exporter\Parser;

use DateTime;

/**
 * Abstract batch exporter parser
 *
 * Class AbstractBatchParser
 * @package Application\ImportBundle\Generator\Exporter\Parser
 */
abstract class AbstractBatchParser implements BatchParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $config)
    {
        $columns = array(
            'id',
            'type',
            'date_created',
            'date_modified',
        );

        return $this->hasRequiredColumns($config, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $config)
    {
        $batch_config = $this->getDefaultBatchConfig();
        $batch_config->setId($config['id']);

        if ($config['date_created']) {
            $batch_config->setDateCreated(new DateTime($config['date_created']));
        }
        if ($config['date_modified']) {
            $batch_config->setDateModified(new DateTime($config['date_modified']));
        }

        return $batch_config;
    }

    /**
     * Check if a config has all required columns
     *
     * @param array   $config
     * @param array   $columns
     * @param boolean $throw_exception
     *
     * @return bool
     * @throws NoColumnException
     */
    protected function hasRequiredColumns(array $config, array $columns, $throw_exception = true)
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $config) === false) {
                if ($throw_exception) {
                    throw new NoColumnException(sprintf('Column `%s` not found', $column));
                }

                return false;
            }
        }

        return true;
    }
}
