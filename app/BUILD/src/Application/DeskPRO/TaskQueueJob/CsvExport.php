<?php

/**
 * DeskPRO.
 *
 * @category TaskQueueJob
 */

namespace Application\DeskPRO\TaskQueueJob;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\TmpData;
use Symfony\Component\Process\Process;

/**
 * Class CsvExport.
 */
class CsvExport extends AbstractJob
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'CSV Export';
    }

    /**
     * {@inheritdoc}
     */
    public function run($max_time)
    {
        $em = App::getOrm();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $start_time = microtime(true);
        $file       = $this->data['file'];
        $delimeter  = ',';
        $enclosure  = '"';

        if (!$file) {
            if (!is_dir(dp_get_tmp_dir().'/export')) {
                mkdir(dp_get_tmp_dir().'/export');
            }
            $file = dp_get_tmp_dir().'/export/DP-export-'.date('Ymd-His').'.csv';
        }

        $this->getContactDataHeaders();
        $this->getCustomFieldsHeaders();
        $fp = fopen($file, 'a');

        if (!$this->data['file']) {

            //select max(cnt) from (select count(person_id) as cnt from people_contact_data group by person_id) as counts
            /*
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
            $headers = array_merge($this->data['headers'], $this->data['contact_headers'], $this->data['custom_fields_headers']);
            fputcsv($fp, $headers, $delimeter, $enclosure);
            $this->data['file'] = $file;
        }

        /** @var \Application\DeskPRO\EntityRepository\Person $rep */
        $rep = $em->getRepository(Person::class);

        while (microtime(true) - $start_time < $max_time) {
            if (!$batch = $rep->findBy([], [], $this->data['limit'], $this->data['offset'])) {
                break;
            }

            foreach ($batch as $person) {
                if (microtime(true) >= $max_time + $start_time) {
                    break;
                }

                /* @var $person Person */
                $row = [
                    $person['id'],
                    implode(',', [$person['name'], $person['first_name'], $person['last_name']]),
                    $person['title_prefix'],
                    $person->getPrimaryEmailAddress(),
                    implode(',', $person->getEmailAddresses(true)),
                    $person->organization ? $person->organization['name'] : '',
                    $person['organization_position'],
                    $person['date_created'] ? $person['date_created']->format('Y-m-d H:i:s') : '',
                    $person['date_last_login'] ? $person['date_last_login']->format('Y-m-d H:i:s') : '',
                    implode(',', $person->getUsergroupIds()),
                    $person->getTimezone(),
                    implode(',', $person->getLabelManager()->getLabelsArray()),
                ];

                $this->fillWithContactData($person, $row);
                $this->fillCustomFieldsValues($person, $row);

                foreach ($row as &$col) {
                    if ('' === $col || null === $col) {
                        $col = ' ';
                    }
                }

                fputcsv($fp, $row, $delimeter, $enclosure);

                ++$this->data['offset'];
                $em->detach($person);
                $person->clear();
                unset($person);
            }
        }

        fclose($fp);

        if ($this->getLogger()) {
            $this->getLogger()->logDebug(sprintf('Exported %d people', $this->data['offset']));
        }

        $task = $this->getTask();

        $task['run_status'] = 'Processed '.$this->data['offset'];
        $task['task_data']  = array_merge($task['task_data'], $this->data);

        if (!$batch) {
            $this->getLogger()->logDebug(sprintf('File: %s', $file));

            if (defined('DPC_IS_CLOUD')) {
                $fname = basename($file);

                $this->getLogger()->logDebug("Zipping: zip -j $fname.zip $fname");

                $proc = new Process("zip -j $fname.zip $fname", dirname($file));
                $proc->setTimeout(300);
                $proc->run();

                if ($proc->isSuccessful()) {
                    $this->getLogger()->logDebug('Zip success');
                    $blob = App::$container->getBlobStorage()->createBlobRecordFromFile(
                        $file.'.zip',
                        'export.csv.zip',
                        'text/csv'
                    );
                    $data = TmpData::create(
                        'csv_export.file',
                        ['file' => $file, 'url' => $blob->getDownloadUrl(true), 'count' => $this->data['offset']],
                        '+28 hours'
                    );
                } else {
                    $out = $proc->getOutput()."\n\n".$proc->getErrorOutput();
                    $this->getLogger()->logDebug('Zip failed: '.$out);
                    $blob = App::$container->getBlobStorage()->createBlobRecordFromString(
                        "There was a problem generating the export. Output:\n\n".$out,
                        'error.txt',
                        'text/plain'
                    );
                    $data = TmpData::create(
                        'csv_export.file',
                        ['file' => $file, 'url' => $blob->getDownloadUrl(true), 'count' => $this->data['offset']],
                        '+28 hours'
                    );
                }
            } else {
                $data = TmpData::create('csv_export.file', ['file' => $file, 'count' => $this->data['offset']], '+28 hours');
            }

            $data['name'] = 'csv_export.file';
            $em->persist($data);

            return self::TASK_COMPLETED;
        } else {
            return self::TASK_CONTINUING;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultData()
    {
        return [
            'file'    => null,
            'offset'  => 0,
            'limit'   => 50,
            'headers' => [
                'ID', 'Name', 'Title', 'Primary Email', 'Additional Emails', 'Organization', 'Org Position',
                'Date Created', 'Date Last Login', 'Usergroup IDs', 'Timezone', 'Labels',
            ],
            'contact_headers'       => [],
            'custom_fields_headers' => [],
        ];
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return mixed
     */
    protected function getContactDataHeaders()
    {
        if ($this->data['contact_headers']) {
            return $this->data['contact_headers'];
        }

        $ch = [
            'address', 'facebook', 'fax', 'instant_message', 'linked_in', 'mobile', 'phone', 'skype', 'twitter', 'website',
        ];

        foreach ($ch as $type) {
            $q   = 'select max(cnt) from (select count(person_id) as cnt from people_contact_data where contact_type = :type group by person_id) as counts';
            $res = App::getOrm()->getConnection()->executeQuery($q, ['type' => $type])->fetchColumn();
            if (!$res) {
                continue;
            }

            for ($i = 1; $i <= (int) $res; ++$i) {
                $this->data['contact_headers'][] = $type;
            }
        }

        return $this->data['contact_headers'];
    }

    /**
     * @return mixed
     */
    protected function getCustomFieldsHeaders()
    {
        if ($this->data['custom_fields_headers']) {
            return $this->data['custom_fields_headers'];
        }

        $field_manager = App::$container->getPersonFieldManager();
        foreach ($field_manager->getFields() as $def) {
            /* @var $def CustomDefPerson */
            $this->data['custom_fields_headers'][] = $def->getTitle();
        }

        return $this->data['custom_fields_headers'];
    }

    /**
     * @param Person $person
     * @param $row
     */
    protected function fillWithContactData(Person $person, &$row)
    {
        $types = [];
        foreach ($person->getContactData() as $cd) {
            /* @var $cd PersonContactData */
            $data = ('phone' === $cd['contact_type'] || 'mobile' === $cd['contact_type'])
                ? $cd['field_1'].$cd['field_2']
                : $cd->getSearchString(true);

            $types[$cd['contact_type']][] = $data;
        }

        foreach ($this->data['contact_headers'] as $type) {
            $val   = isset($types[$type]) ? array_shift($types[$type]) : false;
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
        $data          = $field_manager->getFieldDataForObject($person);
        foreach ($field_manager->getFields() as $def) {
            /* @var $def CustomDefPerson */
            $row[] = isset($data[$def['id']])
                ? trim($def->getHandler()->renderText($data[$def['id']]))
                : '';
        }
    }
}
