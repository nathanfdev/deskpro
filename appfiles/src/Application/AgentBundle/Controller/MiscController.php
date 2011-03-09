<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Util;
use \Orb\Util\Arrays;

class MiscController extends AbstractController
{
	public function getInterfaceDataAction()
	{
		$js = array();

		// Common names
		$js[] = 'window.DESKPRO_NAME_REGISTRY = {};';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.agent = ' . json_encode(App::getEntityRepository('DeskPRO:Person')->getAgentNames()) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.agent_team = ' . json_encode(App::getEntityRepository('DeskPRO:AgentTeam')->getTeamNames()) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.department = ' . json_encode(App::getEntityRepository('DeskPRO:Department')->getDepartmentNames(null, true)) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.department_full = ' . json_encode(App::getEntityRepository('DeskPRO:Department')->getFullDepartmentNames(null, true)) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.product = ' . json_encode(App::getEntityRepository('DeskPRO:Product')->getProductNames()) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category = ' . json_encode(App::getEntityRepository('DeskPRO:TicketCategory')->getCategoryNames()) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category_full = ' . json_encode(App::getEntityRepository('DeskPRO:TicketCategory')->getFullCategoryNames(null, true)) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_priority = ' . json_encode(App::getEntityRepository('DeskPRO:TicketPriority')->getPriorityNames()) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_workflow = ' . json_encode(App::getEntityRepository('DeskPRO:TicketWorkflow')->getWorkflowNames()) . ';';

		// Common URLs
		$js[] = 'window.DESKPRO_URL_REGISTRY = {};';
		$js[] = 'window.DESKPRO_URL_REGISTRY.serve_person_picture = ' . json_encode(str_replace(
			array('000'),
			array('$person_id'),
			$this->generateUrl('serve_person_picture_size', array('person_id' => '000'))
		)) . ';';
		$js[] = 'window.DESKPRO_URL_REGISTRY.serve_person_picture_size = ' . json_encode(str_replace(
			array('000', '111'),
			array('{person_id}', '{size}'),
			$this->generateUrl('serve_person_picture_size', array('person_id' => '000', 'size' => '111'))
		)) . ';';

		// Data
		$js[] = 'window.DESKPRO_DATA_REGISTRY = {}';
		$js[] = 'window.DESKPRO_DATA_REGISTRY.ticketDepToCatMap = ' . json_encode(App::getEntityRepository('DeskPRO:TicketCategory')->departmentToCategoryMap()) . ';';

		$system_filters = App::getDb()->fetchAllKeyValue("SELECT id, sys_name FROM ticket_filters WHERE is_global=1 AND sys_name IS NOT NULL");
		$system_filters = Arrays::castToType($system_filters, 'string', 'int');
		$js[] = 'window.DESKPRO_DATA_REGISTRY.systemFilters = ' . json_encode($system_filters) . ';';

		// Ticket display elements
		$js[] = "window.DESKPRO_TICKET_DISPLAY = {};";
		$display_elements = App::getOrm()->createQuery("
			SELECT d
			FROM DeskPRO:DepartmentTicketDisplay d
			ORDER BY d.display_order ASC
		")->execute();
		$done_deps = array();
		foreach ($display_elements as $d) {
			if (!in_array($d['department_id'], $done_deps)) {
				$done_deps[] = $d['department_id'];
				$js[] = "window.DESKPRO_TICKET_DISPLAY[{$d['department_id']}] = [];";
			}

			$token = '%%%replacetoken' . mt_rand(1000,9999) . '%%%';

			$line = "window.DESKPRO_TICKET_DISPLAY[{$d['department_id']}].push(" . json_encode(array(
				'element_type' => $d['element_type'],
				'element_id' => $d['element_id'],
				'initial_state' => $d['initial_state'],
				'check' => $token
			)) . ");";

			// Cheap and simple way to insert a function literal while still using json_encode for the other values
			$line = str_replace('"'.$token.'"', $d->compileToJavascript(), $line);
			$js[] = $line;
		}

		// Custom field rules
		$js[] = "window.DESKPRO_CUSTOM_TICKET_DEF_RULES = [];";
		$js = implode("\n", $js);

		$response = $this->response;
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}

	public function ajaxSavePrefsAction()
	{
		foreach ($this->in->getCleanValueArray('prefs', 'raw', 'string') as $pref_name => $value)
		{
			$pref = App::getOrm()->getRepository('DeskPRO:PersonPref')->find(array('person_id' => $this->person['id'], 'name' => $pref_name));
			if (!$pref) {
				$pref = new Entity\PersonPref();
				$pref['name'] = $pref_name;
				$this->person->addPreference($pref);
			}

			$pref['value'] = $value;
			App::getOrm()->persist($pref);
		}

		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function ajaxLabelsAutocompleteAction($label_type)
	{
		$search = $this->in->getString('term');
		$statement = App::getDb()->executeQuery("
			SELECT label
			FROM label_defs
			WHERE label_type = ? AND label LIKE ?
			ORDER BY label ASC
			LIMIT 50",
		array($label_type, '%'.$search.'%'));

		$array = array();

		while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
			$array[] = array('name' => $row['label'], 'value' => $row['label']);
		}

		return $this->createJsonResponse($array);
	}

	public function showBlobAction($blob_id)
	{
		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

		$response = $this->container->get('response');
		$response->headers->set('Content-Type', $blob['content_type'] . '; filename=' . $blob['filename']);
		$response->headers->set('Content-Length', $blob['filesize']);
		$response->headers->set('Content-Disposition', 'inline; filename=' . $blob['filename']);

		$desc = App::getApi('filestorage')->getFileDescriptor($blob['id']);
		$response->setContent($desc->get());

		return $response;
	}

    public function acceptTempUploadAction()
    {
		$file = $this->request->files->get('file');
		$desc = App::getApi('filestorage')->createRandomPath();

		$desc->write(file_get_contents($file->getPath()), array(
			'content_type' => $file->getMimeType(),
			'filename' => $file->getOriginalName()
		));

		$blob_id = $desc->getPath();
		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

		return $this->createJsonResponse(array(
			'blob_id' => $blob['id'],
			'download_url' => $blob->getDownloadUrl(true),
			'filename' => $blob['filename'],
			'filesize_readable' => $blob->getReadableFilesize()
		));
	}
}
