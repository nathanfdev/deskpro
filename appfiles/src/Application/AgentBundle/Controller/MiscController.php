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
		$js[] = 'window.DESKPRO_NAME_REGISTRY.department = ' . json_encode(App::getEntityRepository('DeskPRO:Department')->getFlatDepartmentNames(null, true)) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.product = ' . json_encode(App::getEntityRepository('DeskPRO:Product')->getProductNames()) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category = ' . json_encode(App::getEntityRepository('DeskPRO:TicketCategory')->getAllCategoryNames()) . ';';
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
		$targetDir = Util::coalesce(ini_get("upload_tmp_dir"), sys_get_temp_dir()) . DIRECTORY_SEPARATOR . "dpupload";
		$cleanupTargetDir = false; // Remove old files
		$maxFileAge = 60 * 60 * 3; // Temp file age in seconds

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

		// Remove old temp files
		if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// Remove files if they are older than the max age
				if (filemtime($filePath) < time() - $maxFileAge) {
					@unlink($filePath);
				}
			}

			closedir($dir);
		} else {
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory: '.$targetDir.'"}, "id" : "id"}');
		}

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		}
		if (isset($_SERVER["CONTENT_TYPE"])) {
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }
}
