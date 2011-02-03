<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Util;

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

		// Custom field rules
		$js[] = "window.DESKPRO_CUSTOM_TICKET_DEF_RULES = [];";
		$all_rules = App::getOrm()->createQuery("
			SELECT r
			FROM DeskPRO:CustomDefTicketRule r
			ORDER BY r.run_order ASC
		")->execute();
		$done_deps = array();
		foreach ($all_rules as $rule) {
			if (!in_array($rule['department']['id'], $done_deps)) {
				$done_deps[] = $rule['department']['id'];
				//$js[] = "window.DESKPRO_CUSTOM_TICKET_DEF_RULES[" . $rule['department']['id'] . "] = []";
			}
			$js[] = "window.DESKPRO_CUSTOM_TICKET_DEF_RULES.push(" . $rule->compileToJavascript() . ");";
		}

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

	public function ajaxLabelsAutocompleteAction()
	{
		$search = $this->in->getString('term');
		$statement = App::getDb()->executeQuery("SELECT label FROM label_defs WHERE label LIKE ? ORDER BY label ASC LIMIT 50", array('%'.$search.'%'));
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
