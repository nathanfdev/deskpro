<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

/**
 * Handles setting up import processes
 */
class ImportController extends AbstractController
{
	public function indexAction()
	{
		$error = $this->in->getString('error');
		$success = $this->in->getString('success');
		$task_id = $this->in->getUint('task');

		return $this->render('AdminBundle:Import:csv-upload.html.twig', array(
			'error' => $error,
			'success' => $success,
			'task_id' => $task_id
		));
	}

	public function csvConfigureAction()
	{
		$this->ensureRequestToken();

		/** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
		$file = $this->request->files->get('upload');
		if (!$file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile || !$file->getSize()) {
			return $this->redirectRoute('admin_import', array('error' => 'no_file'));
		}

		$filename = 'csv-import-' . microtime(true) . '.csv';
		$csv_path = dp_get_tmp_dir() . '/' . $filename;

		if (!move_uploaded_file($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename(), $csv_path)) {
			return $this->redirectRoute('admin_import', array('error' => 'no_move'));
		}

		return $this->_renderCsvConfigureForm($filename);
	}

	public function csvImportAction()
	{
		$this->ensureRequestToken();

		$field_maps = $this->in->getCleanValueArray('field_maps', 'raw', 'uint');
		$filename = $this->in->getString('filename');

		$has_email = false;
		foreach ($field_maps AS $map_field) {
			if ($map_field['map'] == 'primary_email') {
				$has_email = true;
				break;
			}
		}

		if (!$has_email) {
			return $this->_renderCsvConfigureForm($filename);
		}

		$task = $this->em->getRepository('DeskPRO:TaskQueue')->enqueueTask(
			'Application\\DeskPRO\\TaskQueueJob\\CsvImport',
			array('filename' => $filename, 'field_maps' => $field_maps),
			'data_import'
		);

		return $this->redirectRoute('admin_import', array('success' => 'inserted', 'task' => $task->id));
	}

	protected function _renderCsvConfigureForm($filename)
	{
		$csv_path = dp_get_tmp_dir() . '/' . $filename;

		$fp = fopen($csv_path, 'r');
		$columns = fgetcsv($fp);
		$column_count = count($columns);

		$examples = array();
		$example_total = 0;

		for ($i = 0; $i < 100; $i++) {
			$row = fgetcsv($fp);
			if (isset($row[0]) && $row[0] === null) {
				// empty row
				continue;
			}
			foreach ($row AS $id => $value) {
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

		$custom_fields = App::getApi('custom_fields.people')->getEnabledFields();
		foreach ($custom_fields AS $key => $field) {
			if ($field->isChoiceType()) {
				unset($custom_fields[$key]);
			}
		}

		return $this->render('AdminBundle:Import:csv-configure.html.twig', array(
			'filename' => $filename,
			'columns' => $columns,
			'examples' => $examples,
			'custom_fields' => $custom_fields
		));
	}
}
