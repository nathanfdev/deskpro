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

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\CsvUpload;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\TaskQueue;
use Application\DeskPRO\TaskQueueJob\CsvImport;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvUpload
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
     */

    protected $em;

    /**
     * @param EntityManager $em
     */

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param UploadedFile $file
     *
     * @return array
     */

    public function upload(UploadedFile $file, array $options = array())
    {
        if (!$file instanceof UploadedFile || !$file->getSize()) {
            return array('error' => 'no_file');
        }

        if (!is_uploaded_file($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename())) {
            return array('error' => 'no_move');
        }

        $blob = App::getContainer()->getBlobStorage()->createBlobRecordFromFile(
            $file->getPath().DIRECTORY_SEPARATOR.$file->getFilename(),
            $file->getClientOriginalName(),
            'text/csv'
        );

        $csv_path = dp_get_tmp_dir().'/blob-'.$blob->getId().'.csv';
        copy($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename(), $csv_path);

        return $this->_returnUploadFileResponse($blob->getId(), $file->getClientOriginalName(), $options);
    }

    /**
     * @param array   $field_maps
     * @param string  $filename
     * @param string  $user_filename
     * @param boolean $skip_first
     *
     * @return array
     */

    public function startImportTask($field_maps, $filename, $user_filename, $skip_first, $welcome_email, array $options = array())
    {
        $has_email = false;

        foreach ($field_maps as $map_field) {
            if (!empty($map_field['map']) && $map_field['map'] == 'primary_email') {
                $has_email = true;
                break;
            }
        }

        if (!$has_email) {
            return array('error' => 'no_email');
        }

        $blob = App::getOrm()->find('DeskPRO:Blob', $filename);

        if (!$blob) {
            return array('error' => 'no_move');
        }

        $task_data = array(
            'blob_id'       => $blob->getId(),
            'field_maps'    => $field_maps,
            'skip_first'    => $skip_first,
            'welcome_email' => $welcome_email,
            'user_filename' => $user_filename,
            'options'       => $options,
        );

        $this->em->getRepository('DeskPRO:TaskQueue')->enqueueTask(
            'Application\\DeskPRO\\TaskQueueJob\\CsvImport',
            $task_data,
            'data_import'
        );

        return array('success' => 'task_started');
    }

    /**
     * @return array
     */

    public function returnStatusOfImport()
    {
        $tasks = $this->em->getRepository('DeskPRO:TaskQueue')->getTasksInGroup('data_import', true);

        if (!count($tasks)) {
            return array(
                'status'  => '',
                'message' => 'No import data available.',
            );
        } else {
            /** @var TaskQueue $task */
            $task   = end($tasks);
            $data   = $task['task_data'];

            if ('completed' === $task['status'] || 'errored' === $task['status']) {
                /** @var Blob $logBlob */
                $logBlob = $this->em->find('DeskPRO:Blob', $data['log_blob_id']);

                return array(
                    'status'   => 'completed',
                    'message'  => $task['run_status'],
                    'imported' => $data['imported'],
                    'failed'   => $data['failed'],
                    'log'      => $logBlob ? $logBlob->getDownloadUrl(true) : null,
                );
            }

            return array(
                'status'  => 'progress',
                'message' => $task['run_status'] ?: 'Import will start in 1 minute',
            );
        }
    }

    /**
     * @param string $filename
     * @param string $user_filename
     *
     * @return array
     */

    protected function _returnUploadFileResponse($filename, $user_filename, array $options = array())
    {
        $csv_path = dp_get_tmp_dir().'/blob-'.$filename.'.csv';
        $blob     = App::getOrm()->find('DeskPRO:Blob', $filename);

        if (!$blob) {
            return array('error' => 'no_move');
        }

        if (!is_file($csv_path)) {
            App::getContainer()->getBlobStorage()->copyBlobRecordToFile($csv_path, $blob);
        }

        $originalOptions = $options;
        $options         = CsvImport::getOptions($options);
        $fp              = fopen($csv_path, 'r');
        $columns         = @fgetcsv($fp, null, $options['delimeter'], $options['enclosure']);
        $column_count    = count($columns);

        $examples      = array();
        $example_total = 0;

        for ($i = 0; $i < 100; $i++) {
            $row = @fgetcsv($fp, null, $options['delimeter'], $options['enclosure']);

            if (!$row) {
                // eof or can't read properly
                break;
            }

            if (isset($row[0]) && $row[0] === null) {
                // empty row
                continue;
            }

            foreach ($row as $id => $value) {
                if ($value !== '' && !isset($examples[$id])) {
                    $examples[$id] = $value;
                    $example_total++;

                    if ($example_total == $column_count) {
                        // have example for all columns
                        break 2;
                    }
                }
            }
        }

        fclose($fp);

        $custom_fields      = App::getApi('custom_fields.people')->getEnabledFields();
        $show_welcome_email = !defined('DPC_IS_CLOUD');

        return array(
            'filename'           => $filename,
            'user_filename'      => $user_filename,
            'columns'            => $columns,
            'examples'           => $examples,
            'custom_fields'      => $custom_fields,
            'show_welcome_email' => $show_welcome_email,
            'options'            => $originalOptions,
        );
    }
}
