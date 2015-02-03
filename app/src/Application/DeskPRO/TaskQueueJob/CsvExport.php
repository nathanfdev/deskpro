<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category TaskQueueJob
 */

namespace Application\DeskPRO\TaskQueueJob;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;

class CsvExport extends AbstractJob
{
    public function getTitle()
    {
        return 'CSV Export';
    }

    protected function _getDefaultData()
    {
        return array(
            'file' => null,
            'offset' => 0,
            'limit' => 100,
        );
    }

    public function run($max_time)
    {
        $start_time = microtime(true);
        $file = $this->_data['file'];
        $delimeter = ';';
        $enclosure = '"';

        if (!$file) {
            if (!is_dir(dp_get_tmp_dir() . '/export')) {
                mkdir(dp_get_tmp_dir() . '/export');
            }
            $file = dp_get_tmp_dir() . '/export/DP-export-' . date('Ymd-His') . '.csv';
        }

        $fp = fopen($file, 'a');

        if (!$this->_data['file']) {
            /**
             *  ID
             *  Name (all three fields)
             *  TItle
             *  Primary Email
             *  Additional Emails (comma-separated)
             *  Organization
             *  Org Position
             *  Dates (date_created, date_last_login)
             *  Usergroup (IDs, comma-separated)
             *  Contact Information (flatten to one per column, eg "address", "phone", "msn" etc)
             *  Custom Fields (Flattened to 1 per column)
             *  Timezone
             *  Labels (comma separated)
             */
            fputcsv($fp, array(
                'ID', 'Name', 'Title', 'Primary Email', 'Additional Emails', 'Organization', 'Org Position',
                'Date Created', 'Date Last Login', 'Usergroup IDs', 'Contact Information', 'Custom Fields',
                'Timezone', 'Labels',
            ), $delimeter, $enclosure);
            $this->_data['file'] = $file;
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $rep */
        $rep = App::getOrm()->getRepository('DeskPRO:Person');

        while (microtime(true) - $start_time < $max_time) {

            if (!$batch = $rep->findBy(array(), array(), $this->_data['limit'], $this->_data['offset'])) {
                break;
            }

            foreach ($batch as $person) {
                if (microtime(true) - $start_time >= $max_time) break;
                /** @var $person Person */

                $row = array(
                    $person['id'],
                    implode(',', array($person['name'], $person['first_name'], $person['last_name'])),
                    $person['title_prefix'],
                    $person->getPrimaryEmailAddress(),
                    implode(',', $person->getEmailAddresses()),
                    $person->organization ? $person->organization['name'] : '',
                    $person['organization_position'],
                    $person['date_created'] ? $person['date_created']->format('Y-m-d H:i:s') : '',
                    $person['date_last_login'] ? $person['date_last_login']->format('Y-m-d H:i:s') : '',
                    implode(',', $person->getUsergroupIds()),
                    'contact data',
                    'custom fields',
                    $person->getTimezone(),
                    implode(',', $person->getLabelManager()->getLabelsArray()),
                );

                fputcsv($fp, $row, $delimeter, $enclosure);
                $this->_data['offset']++;
            }
        }

        fclose($fp);

        if ($this->getLogger()) {
            $this->getLogger()->logDebug(sprintf('Exported %d people', $this->_data['offset']));
        }

        $task = $this->getTask();

        $task['run_status'] = 'Processed ' . $this->_data['offset'];
        $task['task_data'] = array_merge($task['task_data'], $this->_data);

        if (!$batch) {

            $data = TmpData::create(
                'csv_export.file',
                array('file' => $file, 'count' => $this->_data['offset']),
                '+24 hours'
            );
            $data['name'] = 'csv_export.file';
            $task['task_data']['tmp'] = $data;
            App::getOrm()->persist($data);

            return self::TASK_COMPLETED;
        } else {
            return self::TASK_CONTINUING;
        }
    }
}
