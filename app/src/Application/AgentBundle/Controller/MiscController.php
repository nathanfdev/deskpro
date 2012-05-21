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
*/

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\AgentBundle\FragmentRouter;

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

class MiscController extends AbstractController
{
	public function getInterfaceDataAction()
	{
		$js = array();

		// Common names
		$js[] = 'window.DESKPRO_NAME_REGISTRY = {};';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.agent = ' . json_encode($this->container->getDataService('Person')->getAgentNames()) . ';';
		if ($this->container->getSetting('core.use_agent_team')) {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.agent_team = ' . json_encode($this->container->getDataService('AgentTeam')->getTeamNames()) . ';';
		} else {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.agent_team = {};';
		}
		$js[] = 'window.DESKPRO_NAME_REGISTRY.department = ' . json_encode($this->container->getDataService('Department')->getNames(null, true)) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.department_full = ' . json_encode($this->container->getDataService('Department')->getFullNames(null, true)) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.department_hierarchy = ' . json_encode($this->container->getDataService('Department')->getInHierarchy(null, true)) . ';';
		if ($this->container->getSetting('core.use_product')) {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.product = ' . json_encode($this->container->getDataService('Product')->getNames()) . ';';
		} else {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.product = {};';
		}
		if ($this->container->getSetting('core.use_ticket_category')) {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category = ' . json_encode($this->container->getDataService('TicketCategory')->getNames()) . ';';
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category_full = ' . json_encode($this->container->getDataService('TicketCategory')->getFullNames(null, true)) . ';';
		} else {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category = {};';
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category_full = {};';
		}
		if ($this->container->getSetting('core.use_ticket_category')) {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_priority = ' . json_encode($this->container->getDataService('TicketPriority')->getNames()) . ';';
		} else {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_priority = {};';
		}
		if ($this->container->getSetting('core.use_ticket_workflow')) {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_workflow = ' . json_encode($this->container->getDataService('TicketWorkflow')->getNames()) . ';';
		} else {
			$js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_workflow = {};';
		}

		$js[] = 'window.DESKPRO_NAME_REGISTRY.status = ' . json_encode(array(
			'awaiting_agent' => App::getTranslator()->phrase('agent.tickets.status_awaiting_agent'),
			'awaiting_user' => App::getTranslator()->phrase('agent.tickets.status_awaiting_user'),
			'hidden' => App::getTranslator()->phrase('agent.tickets.status_hidden'),
			'resolved' => App::getTranslator()->phrase('agent.tickets.status_resolved'),
			'closed' => App::getTranslator()->phrase('agent.tickets.status_closed'),
		)) . ';';
		$js[] = 'window.DESKPRO_NAME_REGISTRY.hidden_status = ' . json_encode(array(
			'deleted' => App::getTranslator()->phrase('agent.tickets.hidden_status_deleted'),
			'spam' => App::getTranslator()->phrase('agent.tickets.hidden_status_spam'),
			'validating' => App::getTranslator()->phrase('agent.tickets.hidden_status_validating'),
		)) . ';';

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

		$system_filters = $this->db->fetchAllKeyValue("SELECT id, sys_name FROM ticket_filters WHERE is_global=1 AND sys_name IS NOT NULL");
		$system_filters = Arrays::castToType($system_filters, 'string', 'int');
		$js[] = 'window.DESKPRO_DATA_REGISTRY.systemFilters = ' . json_encode($system_filters) . ';';

		// Ticket display elements
		$ticket_display = new \Application\DeskPRO\PageDisplay\Page\TicketPageZoneCollection('agent');
		$ticket_display->addPagesFromDb();
		$js[] = "window.DESKPRO_TICKET_DISPLAY = " . $ticket_display->compileJs() . ";";

		$fragment_router = new FragmentRouter($this->get('router')->getGenerator());
		$js[] = $fragment_router->compile();

		$count = $this->em->getRepository('DeskPRO:LabelDef')->countLabels();
		if ($count <= 300) {
			$js[] = "window.DESKPRO_DATA_REGISTRY.labels = " . json_encode($this->em->getRepository('DeskPRO:LabelDef')->getAllLabelsToTyped());
		}

		$tr = $this->container->getTranslator();

		$js[] = <<<JS
function Orb_Util_TimeAgo_getPhraseFor(type, num, ago) {

	var phrasepre = 'reltime';
	if (ago) {
		phrasepre = 'reltimeago';
	}

	if (type == 'min') type = 'minute';
	else if (type == 'mins') type = 'minutes';
	else if (type == 'sec') type = 'second';
	else if (type == 'secs') type = 'seconds';

	var phrasename = 'agent.general.' + phrasepre + '_x_' + type;
	if (num == 1) {
		var phrasename = 'agent.general.' + phrasepre + '_1_' + type;
	}
	if (type == 'sec' && num <= 0) {
		var phrasename = 'agent.general.' + phrasepre + '_less_second';
	}

	if (!DESKPRO_LANG[phrasename]) {
		console.warn("Missing phrase %s", phrasename);
	}

	return (DESKPRO_LANG[phrasename] || "").replace(/\{0\}/g, num);
}
JS;

		$js = implode("\n", $js);

		$response = $this->response;
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}

	public function ajaxSavePrefsAction()
	{
		$prefs_expire = $this->in->getCleanValueArray('prefs_expire', 'raw', 'string');

		foreach ($this->in->getCleanValueArray('prefs', 'raw', 'string') as $pref_name => $value)
		{
			$pref = $this->em->getRepository('DeskPRO:PersonPref')->find(array('person' => $this->person['id'], 'name' => $pref_name));
			if (!$pref) {
				$pref = new Entity\PersonPref();
				$pref['name'] = $pref_name;
				$this->person->addPreference($pref);
			}

			if (isset($prefs_expire[$pref_name])) {
				$date = new \DateTime($prefs_expire[$pref_name]);
				$pref['date_expire'] = $date;
			}

			$pref['value'] = $value;
			$this->em->persist($pref);
		}

		$this->em->flush();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function ajaxSaveStateAction()
	{
		$value = array();
		$value['tabs'] = $this->in->getCleanValueArray('tabs', 'raw', 'discard');

		$pref = $this->em->getRepository('DeskPRO:PersonPref')->find(array('person' => $this->person['id'], 'name' => 'agent.ui.state'));
		if (!$pref) {
			$pref = new Entity\PersonPref();
			$pref['name'] = 'agent.ui.state';
			$this->person->addPreference($pref);
		}
		$pref['value'] = $value;

		$this->em->persist($pref);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	public function ajaxLabelsAutocompleteAction($label_type)
	{
		$search = $this->in->getString('term');
		$statement = $this->db->executeQuery("
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
		$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

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
		$file = $this->request->files->get('file-upload');
		$accept = $this->container->getAttachmentAccepter();

		$error = $accept->getError($file, 'agent');
		if ($error) {
			$error['error'] = $this->container->getTranslator()->phrase('agent.general.attach_error_' . $error['error_code'], $error);
			return $this->createJsonResponse(array($error));
		}

		$blob = $accept->accept($file);

		if ($this->in->getString('attach_to_object')) {
			switch ($this->in->getString('attach_to_object')) {
				case 'article':
					$article = $this->em->find('DeskPRO:Article', $this->in->getUint('object_id'));

					$attach = new \Application\DeskPRO\Entity\ArticleAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $this->person;

					$article->addAttachment($attach);

					$this->em->persist($attach);
					$this->em->persist($article);
					$this->em->flush();

					break;

				case 'feedback':
					$feedback = $this->em->find('DeskPRO:Feedback', $this->in->getUint('object_id'));

					$attach = new \Application\DeskPRO\Entity\FeedbackAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $this->person;

					$feedback->addAttachment($attach);
					$this->em->persist($attach);
					$this->em->persist($feedback);
					$this->em->flush();

					break;
			}
		}

		$res = $this->createJsonResponse(array(array(
			'blob_id'           => $blob['id'],
			'blob_auth'         => $blob->authcode,
			'blob_auth_id'      => $blob->id . '-' . $blob->authcode,
			'download_url'      => $blob->getDownloadUrl(true),
			'filename'          => $blob['filename'],
			'filesize_readable' => $blob->getReadableFilesize()
		)));

		// Required for iframe transport on IE to prevent 'download' popup
		$res->headers->set('Content-Type', 'text/plain');
		return $res;
	}

    public function parseVCardAction()
    {
        $file = $this->request->files->get('files');

        $content = file_get_contents($file[0]->getPathName());
        $parse = \File_IMC::parse('vCard');
        $vcard = $parse->fromText($content);
        $fields = array();

        if(isset($vcard['VCARD'])) {
            foreach($vcard['VCARD'] as $vc) {

                if(isset($vc['EMAIL'])
                && isset($vc['EMAIL'][0]['value'])) {
                    $fields['email'] = $vc['EMAIL'][0]['value'][0][0];
                }

                if(isset($vc['FN'])
                && isset($vc['FN'][0]['value'])) {
                    $fields['name'] = $vc['FN'][0]['value'][0][0];
                }
            }
        }

        $res = $this->createJsonResponse(array(array('fields' => $fields)));

        // Required for iframe transport on IE to prevent 'download' popup
        $res->headers->set('Content-Type', 'text/plain');
        return $res;
    }

	/**
	 * @param  $id
	 * @return void
	 */
	public function dismissHelpMessageAction($id)
	{
		$this->person->HelpMessages->dismiss($id);

		$this->createJsonResponse(array('success' => true));
	}

	/**
	 * Set away status
	 * @param  $status
	 */
	public function setAgentStatusAction($status)
	{
		if (!$status OR $status == 'away') {
			$status = 'away';
		} else {
			$status = 'available';
		}

		$sessionEnt = $this->session->getEntity();
		$sessionEnt['active_status'] = $status;

		$this->em->transactional(function($em) use ($sessionEnt) {
			$em->persist($sessionEnt);
			$em->flush();
		});

		return $this->createJsonResponse(array('success' =>true, 'status' => $status));
	}


	############################################################################
	# snippets-viewer
	############################################################################

	public function snippetsViewerAction($typename)
	{
		$text_snippets = $this->em->getRepository('DeskPRO:TextSnippet')->getSnippetsForAgent($typename, $this->person);
		$text_snippet_cats = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getCatsForAgent($typename, $this->person);

		$agent_teams = $this->em->getRepository('DeskPRO:AgentTeam')->findAll();

		return $this->render('AgentBundle:Common:text-snippets.html.twig', array(
			'text_snippets'      => $text_snippets,
			'text_snippet_cats'  => $text_snippet_cats,
			'agent_teams'        => $agent_teams,
			'typename'           => $typename,
		));
	}

	public function newSnippetCatAction()
	{
		$cat = new \Application\DeskPRO\Entity\TextSnippetCategory();
		$cat['title'] = $this->in->getString('title');
		$cat['typename'] = $this->in->getString('typename');
		$cat->person = $this->person;

		if ($this->in->getString('perm_type') == 'global') {
			$cat['is_global'] = true;
		} elseif ($this->in->getString('perm_type') == 'team') {
			$team_ids = $this->in->getArrayValue('teams');
			$teams = $this->em->getRepository('DeskPRO:AgentTeam')->getTeamsFromIds($team_ids);

			foreach ($teams as $t) {
				$cat->agent_teams->add($t);
			}
		}

		$this->em->transactional(function($em) use ($cat) {
			$em->persist($cat);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'cat_row_html' => $this->renderView('AgentBundle:Common:text-snippets-catrow.html.twig', array(
				'category' => $cat
			)),
			'cat_section_html' => $this->renderView('AgentBundle:Common:text-snippets-catsection.html.twig', array(
				'category' => $cat,
				'snippets' => array()
			))
		));
	}

	public function editSnippetCatAction()
	{
		$cat = $this->em->find('DeskPRO:TextSnippetCategory', $this->in->getUint('category_id'));

		return $this->render('AgentBundle:Common:text-snippets-editcat.html.twig', array(
			'category' => $cat,
		));
	}

	public function saveSnippetCatAction()
	{
		$cat = $this->em->find('DeskPRO:TextSnippetCategory', $this->in->getUint('category_id'));
		$cat['title'] = $this->in->getString('title');

		$this->em->persist($cat);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'category_id' => $cat['id'],
			'title' => $cat['title']
		 ));
	}

	public function deleteSnippetCatAction()
	{
		$cat = $this->em->find('DeskPRO:TextSnippetCategory', $this->in->getUint('category_id'));

		$cat_id = $cat['id'];

		$this->em->transactional(function($em) use ($cat) {
			$em->remove($cat);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'category_id' => $cat_id,
		 ));
	}

	public function saveSnippetAction()
	{
		if ($this->in->getUint('snippet_id')) {
			$snippet = $this->em->find('DeskPRO:TextSnippet', $this->in->getUint('snippet_id'));
			$category = $snippet->category;
		} else {
			$category = $this->em->find('DeskPRO:TextSnippetCategory', $this->in->getUint('category_id'));
			$snippet = new \Application\DeskPRO\Entity\TextSnippet();
			$snippet->category = $category;
		}

		$snippet['title'] = $this->in->getString('title');
		$snippet['snippet'] = $this->in->getString('snippet');
		$snippet->person = $this->person;

		$this->em->transactional(function($em) use ($snippet) {
			$em->persist($snippet);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'snippet_row_html' => $this->renderView('AgentBundle:Common:text-snippets-row.html.twig', array(
				'snippet' => $snippet,
			)),
			'snippet_id' => $snippet['id'],
			'category_id' => $category['id']
		));
	}

	public function deleteSnippetAction()
	{
		$snippet = $this->em->find('DeskPRO:TextSnippet', $this->in->getUint('snippet_id'));

		$snippet_id = $snippet['id'];
		$category_id = $snippet->category['id'];

		$this->em->transactional(function($em) use ($snippet) {
			$em->remove($snippet);
			$em->flush();
		});

		return $this->createJsonResponse(array(
			'snippet_id' => $snippet_id,
			'category_id' => $category_id
		));
	}

	public function redirectExternalAction($url)
	{
		if (!$this->container->getSetting('core.agent_intercept_external_link')) {
			$res = new \Symfony\Component\HttpFoundation\RedirectResponse($url, 302);
			return $res;
		}

		$urlinfo = parse_url($url);

		return $this->render('AgentBundle:Misc:redirect-external.html.twig', array(
			'url' => $url,
			'urlinfo' => $urlinfo
		));
	}

	public function redirectExternalInfoAction($url)
	{
		$urlinfo = parse_url($url);

		$page = @file_get_contents($url);
		$info = array();

		$info['title'] = Strings::extractRegexMatch('#<title>(.*?)</title>#im', $page, 1);
		$info['ip'] = gethostbyname($urlinfo['host']);
		$info['hostname'] = gethostbyname($info['ip']);
		$info['size'] = strlen($page);
		$info['size_readable'] = Numbers::filesizeDisplay($info['size']);

		$info['num_images'] = substr_count($page, '<img');
		$info['num_scripts'] = substr_count($page, '<script');

		return $this->render('AgentBundle:Misc:redirect-external-info.html.twig', array(
			'url' => $url,
			'urlinfo' => $urlinfo,
			'info' => $info,
		));
	}

	public function getPasswordConfirmCodeAction()
	{
		$password = $this->in->getString('password');
		if (!$this->person->checkPassword($password)) {
			return $this->createJsonResponse(array('invalid' => true));
		}

		$code = $this->session->getEntity()->generateSecurityToken('password_confirm' . $this->person->secret_string);
		return $this->createJsonResponse(array('code' => $code));
	}

	public function submitDeskproFeedbackAction()
	{
		\Application\DeskPRO\Service\ErrorReporter::sendFeedback($this->person, $this->in->getString('message'));
		return $this->createJsonResponse(array('success' => true));
	}
}
