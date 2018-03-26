<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CsvUpload;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\TaskQueue;
use Application\DeskPRO\TaskQueueJob\CsvImport;
use Application\DeskPRO\Util;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvUpload
{
    /**
     * @var \Doctrine\ORM\EntityManager
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
    public function upload(UploadedFile $file, array $options = [])
    {
        // TODO proper handling of error message here
        if (defined('DPC_IS_CLOUD') && DPC_DEMO_EXPIRE) {
            return ['error' => 'disabled_in_demo'];
        }

        if (!$file instanceof UploadedFile || !$file->getSize()) {
            return ['error' => 'no_file'];
        }

        if (!is_uploaded_file($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename())) {
            return ['error' => 'no_move'];
        }

        $encoded = Util::jsonEncode(file_get_contents($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename()));
        if (!$encoded) {
            return ['error' => 'mailformed_data'];
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
     * @param array  $field_maps
     * @param string $filename
     * @param string $user_filename
     * @param bool   $skip_first
     *
     * @return array
     */
    public function startImportTask($field_maps, $filename, $user_filename, $skip_first, $welcome_email, $update_if_exists, array $options = [])
    {
        $has_email = false;

        foreach ($field_maps as $map_field) {
            if (!empty($map_field['map']) && $map_field['map'] == 'primary_email') {
                $has_email = true;
                break;
            }
        }

        if (!$has_email) {
            return ['error' => 'no_email'];
        }

        $blob = App::getOrm()->find('DeskPRO:Blob', $filename);

        if (!$blob) {
            return ['error' => 'no_move'];
        }

        $task_data = [
            'blob_id'          => $blob->getId(),
            'field_maps'       => $field_maps,
            'skip_first'       => $skip_first,
            'update_if_exists' => $update_if_exists,
            'welcome_email'    => $welcome_email,
            'user_filename'    => $user_filename,
            'options'          => $options,
        ];

        /** @var \Application\DeskPRO\EntityRepository\TaskQueue $rep */
        $rep = $this->em->getRepository('DeskPRO:TaskQueue');
        // cancel all running imports before starting a new one
        $tasks = $rep->getTasksInGroup('data_import');
        foreach ($tasks as $task) {
            $task->status         = 'completed';
            $task->date_completed = new \DateTime();
            $task->run_status     = 'Cancelled by user';
        }
        $this->em->flush();

        $rep->enqueueTask(
            'Application\\DeskPRO\\TaskQueueJob\\CsvImport',
            $task_data,
            'data_import'
        );

        return ['success' => 'task_started'];
    }

    /**
     * @return array
     */
    public function returnStatusOfImport()
    {
        if (defined('DPC_IS_CLOUD') && DPC_DEMO_EXPIRE) {
            return [
                'status'  => 'disabled_on_demo',
                'message' => '',
            ];
        }

        $tasks = $this->em->getRepository('DeskPRO:TaskQueue')->getTasksInGroup('data_import', true);

        if (!count($tasks)) {
            return [
                'status'  => '',
                'message' => 'No import data available.',
            ];
        } else {
            /** @var TaskQueue $task */
            $task = end($tasks);
            $data = $task['task_data'];

            if ('completed' === $task['status'] || 'errored' === $task['status']) {
                /* @var Blob $logBlob */
                if (!empty($data['log_blob_id'])) {
                    $logBlob = $this->em->find('DeskPRO:Blob', $data['log_blob_id']);
                } else {
                    $logBlob = null;
                }

                return [
                    'status'   => 'completed',
                    'message'  => $task['run_status'],
                    'imported' => @$data['imported'] ?: 0,
                    'failed'   => @$data['failed'] ?: 0,
                    'log'      => $logBlob ? $logBlob->getDownloadUrl(true) : null,
                ];
            }

            return [
                'status'  => 'progress',
                'message' => $task['run_status'] ?: 'Import will start in 1 minute',
            ];
        }
    }

    /**
     * @param string $filename
     * @param string $user_filename
     *
     * @return array
     */
    protected function _returnUploadFileResponse($filename, $user_filename, array $options = [])
    {
        $csv_path = dp_get_tmp_dir().'/blob-'.$filename.'.csv';
        $blob     = App::getOrm()->find('DeskPRO:Blob', $filename);

        if (!$blob) {
            return ['error' => 'no_move'];
        }

        if (!is_file($csv_path)) {
            App::getContainer()->getBlobStorage()->copyBlobRecordToFile($csv_path, $blob);
        }

        $originalOptions = $options;
        $options         = CsvImport::getOptions($options);
        $fp              = fopen($csv_path, 'r');
        $columns         = @fgetcsv($fp, null, $options['delimeter'], $options['enclosure']);
        $column_count    = count($columns);

        $examples      = [];
        $example_total = 0;

        for ($i = 0; $i < 100; ++$i) {
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
                    ++$example_total;

                    if ($example_total == $column_count) {
                        // h$this->getApiData($result)ave example for all columns
                        break 2;
                    }
                }
            }
        }

        fclose($fp);

        $custom_fields      = App::getApi('custom_fields.people')->getEnabledFields();
        $show_welcome_email = !defined('DPC_IS_CLOUD');

        return [
            'filename'           => $filename,
            'user_filename'      => $user_filename,
            'columns'            => $columns,
            'examples'           => $examples,
            'custom_fields'      => $custom_fields,
            'show_welcome_email' => $show_welcome_email,
            'options'            => $originalOptions,
        ];
    }
}
