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
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
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
            'headers' => array(
                'ID', 'Name', 'Title', 'Primary Email', 'Additional Emails', 'Organization', 'Org Position',
                'Date Created', 'Date Last Login', 'Usergroup IDs', 'Timezone', 'Labels',
            ),
            'contact_headers' => array(),
            'custom_fields_headers' => array(),
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

        $this->getContactDataHeaders();
        $this->getCustomFieldsHeaders();
        $fp = fopen($file, 'a');

        if (!$this->_data['file']) {

            //select max(cnt) from (select count(person_id) as cnt from people_contact_data group by person_id) as counts
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
             *  Timezone
             *  Labels (comma separated)
             *
             *  Contact Information (flatten to one per column, eg "address", "phone", "msn" etc)
             *  Custom Fields (Flattened to 1 per column)
             */
            $headers = array_merge($this->_data['headers'], $this->_data['contact_headers'], $this->_data['custom_fields_headers']);
            fputcsv($fp, $headers, $delimeter, $enclosure);
            $this->_data['file'] = $file;
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $rep */
        $rep = App::getOrm()->getRepository('DeskPRO:Person');

        while (microtime(true) - $start_time < $max_time * 10000) {

            if (!$batch = $rep->findBy(array(), array(), $this->_data['limit'], $this->_data['offset'])) {
                break;
            }

            foreach ($batch as $person) {

                if (microtime(true) >= 10000 * $max_time + $start_time) break;
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
                    $person->getTimezone(),
                    implode(',', $person->getLabelManager()->getLabelsArray()),
                );

                $this->fillWithContactData($person, $row);
                $this->fillCustomFieldsValues($person, $row);

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

    /**
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getContactDataHeaders()
    {
        if ($this->_data['contact_headers']) {
            return $this->_data['contact_headers'];
        }

        $ch = array(
            'address', 'facebook', 'fax', 'instant_message', 'linked_in', 'mobile', 'phone', 'skype', 'twitter', 'website',
        );

        foreach ($ch as $type) {
            $q = 'select max(cnt) from (select count(person_id) as cnt from people_contact_data where contact_type = :type group by person_id) as counts';
            $res = App::getOrm()->getConnection()->executeQuery($q, array('type' => $type))->fetchColumn();
            if (!$res) continue;

            for ($i = 1; $i <= (int) $res; $i++) {
                $this->_data['contact_headers'][] = $type;
            }
        }

        return $this->_data['contact_headers'];
    }

    /**
     * @return mixed
     */
    protected function getCustomFieldsHeaders()
    {
        if ($this->_data['custom_fields_headers']) {
            return $this->_data['custom_fields_headers'];
        }

        $field_manager = App::$container->getPersonFieldManager();
        foreach ($field_manager->getFields() as $def) {
            /** @var $def CustomDefPerson */
            $this->_data['custom_fields_headers'][] = $def->getTitle();
        }

        return $this->_data['custom_fields_headers'];
    }

    /**
     * @param Person $person
     * @param $row
     */
    protected function fillWithContactData(Person $person, &$row)
    {
        $types = array();
        foreach ($person->getContactData() as $cd) {
            /** @var $cd PersonContactData */
            $data = ('phone' === $cd['contact_type'] || 'mobile' === $cd['contact_type'])
                ? $cd['field_1'] . $cd['field_2']
                :$cd->getSearchString();

            $types[$cd['contact_type']][] = $data;
        }

        foreach ($this->_data['contact_headers'] as $type) {
            $val = isset($types[$type]) ? array_shift($types[$type]) : false;
            $row[] = (string) $val;
        }
    }

    /**
     * @param Person $person
     * @param $row
     */
    protected function fillCustomFieldsValues(Person $person, &$row)
    {
        $field_manager = App::$container->getPersonFieldManager();
        $data = $field_manager->getFieldDataForObject($person);
        foreach ($field_manager->getFields() as $def) {
            /** @var $def CustomDefPerson */
            $row[] = isset($data[$def['id']])
                ? trim($def->getHandler()->renderText($data[$def['id']]))
                : '';
        }
    }
}
