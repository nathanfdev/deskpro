<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\FragmentRouter;
use Application\DeskPRO\App;
use Application\DeskPRO\App\Assets\RequireJsConfigGenerator as AppsRequireJsConfigGenerator;
use Application\DeskPRO\Assets\RequireJsConfigGenerator;
use Application\DeskPRO\Chat\UserChat\AvailableTrigger;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\People\AgentPermissions\PersonDbLoader as AgentPermsPersonDbLoader;
use Application\DeskPRO\Routing\Generator\UrlGenerator;
use Composer\CaBundle\CaBundle;
use DeskPRO\Bundle\AppBundle\DependencyInjection\SystemServices\EnvironmentService;
use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\AgentStatusChangedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Routing\RouterUtils;
use DeskPRO\Component\Filesystem\SafeFile;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;

class MiscController extends AbstractController
{
    public function requireRequestToken($action, $arguments = null)
    {
        if ($action == 'getInterfaceDataAction' || $action == 'getRequirejsLoaderAction' || $action == 'getAppsConfigAction' || $action == 'userInterfaceFrameAction') {
            return false;
        }

        return parent::requireRequestToken($action, $arguments);
    }

    public function getGeoIpAction(Request $request)
    {
        /** @var EnvironmentService $env */
        $env = $this->container->get('app.environment');

        return $this->createJsonResponse([
            'geoip' => $env->getGeoIp(),
        ]);
    }

    public function getInterfaceDataAction()
    {
        $js = [];

        // Common names
        $js[] = 'window.DESKPRO_NAME_REGISTRY = {};';
        $js[] = 'window.DESKPRO_NAME_REGISTRY.agent = '.json_encode($this->container->getDataService('Person')->getAgentNames()).';';
        if ($this->container->getSetting('core.use_agent_team')) {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.agent_team = '.json_encode($this->container->getDataService('AgentTeam')->getTeamNames()).';';
        } else {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.agent_team = {};';
        }
        $js[] = 'window.DESKPRO_NAME_REGISTRY.department = '.json_encode($this->container->getDataService('Department')->getNames(null, true)).';';
        $js[] = 'window.DESKPRO_NAME_REGISTRY.department_full = '.json_encode($this->container->getDataService('Department')->getFullNames(null, true)).';';
        $js[] = 'window.DESKPRO_NAME_REGISTRY.department_hierarchy = '.json_encode($this->container->getDataService('Department')->getInHierarchy(null, true)).';';
        if ($this->container->getSetting('core.use_product')) {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.product = '.json_encode($this->container->getDataService('Product')->getNames()).';';
        } else {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.product = {};';
        }
        if ($this->container->getSetting('core.use_ticket_category')) {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category = '.json_encode($this->container->getDataService('TicketCategory')->getNames()).';';
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category_full = '.json_encode($this->container->getDataService('TicketCategory')->getFullNames(null, true)).';';
        } else {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category = {};';
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_category_full = {};';
        }
        if ($this->container->getSetting('core.use_ticket_category')) {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_priority = '.json_encode($this->container->getDataService('TicketPriority')->getNames()).';';
        } else {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_priority = {};';
        }
        if ($this->container->getSetting('core.use_ticket_workflow')) {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_workflow = '.json_encode($this->container->getDataService('TicketWorkflow')->getNames()).';';
        } else {
            $js[] = 'window.DESKPRO_NAME_REGISTRY.ticket_workflow = {};';
        }
        $js[] = 'window.DESKPRO_NAME_REGISTRY.language = '.json_encode($this->container->getDataService('Language')->getTitles()).';';

        $lang_data = [];
        foreach ($this->container->getLanguageData()->getAll() as $lang) {
            $lang_data[$lang->id] = [
                'id'         => $lang->id,
                'title'      => $this->container->getTranslator()->getPhraseObject($lang),
                'title_real' => $lang->title,
                'locale'     => $lang->locale,
            ];
        }
        $js[] = 'window.DESKPRO_NAME_REGISTRY.lang_data = '.json_encode($lang_data).';';

        $js[] = 'window.DESKPRO_NAME_REGISTRY.language = '.json_encode($this->container->getDataService('Language')->getTitles()).';';

        $js[] = 'window.DESKPRO_NAME_REGISTRY.status = '.json_encode([
                'awaiting_agent' => App::getTranslator()->phrase('agent.tickets.status_awaiting_agent'),
                'awaiting_user'  => App::getTranslator()->phrase('agent.tickets.status_awaiting_user'),
                'hidden'         => App::getTranslator()->phrase('agent.tickets.status_hidden'),
                'resolved'       => App::getTranslator()->phrase('agent.tickets.status_resolved'),
                'archived'       => App::getTranslator()->phrase('agent.tickets.status_archived'),
            ]).';';
        $js[] = 'window.DESKPRO_NAME_REGISTRY.hidden_status = '.json_encode([
                'deleted' => App::getTranslator()->phrase('agent.tickets.hidden_status_deleted'),
                'spam'    => App::getTranslator()->phrase('agent.tickets.hidden_status_spam'),
            ]).';';

        // Common URLs
        $js[] = 'window.DESKPRO_URL_REGISTRY = {};';
        $js[] = 'window.DESKPRO_URL_REGISTRY.serve_person_picture = '.json_encode(str_replace(
                ['000'],
                ['$person_id'],
                $this->generateUrl('serve_person_picture_size', ['person_id' => '000'])
            )).';';
        $js[] = 'window.DESKPRO_URL_REGISTRY.serve_person_picture_size = '.json_encode(str_replace(
                ['000', '111'],
                ['{person_id}', '{size}'],
                $this->generateUrl('serve_person_picture_size', ['person_id' => '000', 'size' => '111'])
            )).';';

        // Data
        $js[] = 'window.DESKPRO_DATA_REGISTRY = {}';

        $system_filters = $this->db->fetchAllKeyValue('SELECT id, sys_name FROM ticket_filters WHERE is_global=1 AND sys_name IS NOT NULL');
        $system_filters = Arrays::castToType($system_filters, 'string', 'int');
        $js[]           = 'window.DESKPRO_DATA_REGISTRY.systemFilters = '.json_encode($system_filters).';';

        // Ticket display elements
        $layouts = $this->container->getTicketLayoutManager()->getAgentLayouts();
        $js[]    = 'window.DESKPRO_TICKET_DISPLAY = '.$layouts->compileJsObj().';';

        // Snippet short codes
        $snippetShortCodes = [];
        if ($this->container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
            $ticketSnippets = $this->em->getRepository(Snippet::class)->getSnippetsForAgent(
                $this->person,
                Snippet::TYPE_TICKET
            );
            /** @var Snippet $snippet */
            foreach ($ticketSnippets as $snippet) {
                if ($snippet->getShortcutCode()) {
                    if (!isset($snippetShortCodes[$snippet->getShortcutCode()])) {
                        $snippetShortCodes[$snippet->getShortcutCode()] = [];
                    }
                    $snippetShortCodes[$snippet->getShortcutCode()][] = $snippet->getId();
                }
            }
        } else {
            $ticketSnippets = $this->em->getRepository(TextSnippet::class)->getSnippetsForAgent(
                'tickets',
                $this->person
            );
            foreach ($ticketSnippets as $snippetCat) {
                if ($snippetCat['snippets']) {
                    /** @var TextSnippet $snippet */
                    foreach ($snippetCat['snippets'] as $snippet) {
                        if ($snippet->getShortcutCode()) {
                            if (!isset($snippetShortCodes[$snippet->getShortcutCode()])) {
                                $snippetShortCodes[$snippet->getShortcutCode()] = [];
                            }
                            $snippetShortCodes[$snippet->getShortcutCode()][] = $snippet->getId();
                        }
                    }
                }
            }
        }

        if ($snippetShortCodes) {
            $js[] = 'window.DESKPRO_TICKET_SNIPPET_SHORTCODES = '.json_encode($snippetShortCodes).';';
        } else {
            $js[] = 'window.DESKPRO_TICKET_SNIPPET_SHORTCODES = {};';
        }

        // Snippet short codes
        $snippetShortCodes = [];
        if ($this->container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
            $chatSnippets = $this->em->getRepository(Snippet::class)->getSnippetsForAgent(
                $this->person,
                Snippet::TYPE_CHAT
            );
            /** @var Snippet $snippet */
            foreach ($chatSnippets as $snippet) {
                if ($snippet->getShortcutCode()) {
                    if (!isset($snippetShortCodes[$snippet->getShortcutCode()])) {
                        $snippetShortCodes[$snippet->getShortcutCode()] = [];
                    }
                    $snippetShortCodes[$snippet->getShortcutCode()][] = $snippet->getId();
                }
            }
        } else {
            $chatSnippets      = $this->em->getRepository(TextSnippet::class)->getSnippetsForAgent('chat', $this->person);
            $snippetShortCodes = [];
            foreach ($chatSnippets as $snippetCat) {
                if ($snippetCat['snippets']) {
                    /** @var TextSnippet $snippet */
                    foreach ($snippetCat['snippets'] as $snippet) {
                        if ($snippet->getShortcutCode()) {
                            $snippetShortCodes[$snippet->getShortcutCode()] = $snippet->getId();
                        }
                    }
                }
            }
        }

        if ($snippetShortCodes) {
            $js[] = 'window.DESKPRO_CHAT_SNIPPET_SHORTCODES = '.json_encode($snippetShortCodes).';';
        } else {
            $js[] = 'window.DESKPRO_CHAT_SNIPPET_SHORTCODES = {};';
        }

        $js[] = 'window.DESKPRO_TICKET_PRI_MAP = '.json_encode($this->container->getDataService('TicketPriority')->getIdToPriorityMap()).';';

        $router          = RouterUtils::unwrapDecoratedRouter($this->get('router'));
        $fragment_router = new FragmentRouter($router->getGenerator());
        $js[]            = $fragment_router->compile();

        /** @var \Application\DeskPRO\EntityRepository\LabelDef $labelDef */
        $labelDef = $this->em->getRepository('DeskPRO:LabelDef');
        $js[]     = 'window.DESKPRO_DATA_REGISTRY.labels = '.json_encode($labelDef->getAllLabelsToTyped());

        if ($this->container->getAppManager()->isPackageInstalled('deskpro_ms_translator')) {
            $ms_translator = $this->container->getAppManager()->getService('ms_translator');
            $lang_codes    = $ms_translator->getLanguagesForTranslate();
            try {
                $lang_names = $ms_translator->getLanguageNames(
                    $ms_translator->getLanguagesForTranslate(),
                    $this->person->getLanguage()->getLocale()
                );
            } catch (\Exception $e) {
                $lang_names = $ms_translator->getLanguageNames(
                    $ms_translator->getLanguagesForTranslate(),
                    'en'
                );
            }

            $app_id = 0;
            if ($this->container->getAppManager()->isPackageInstalled('deskpro_ms_translator')) {
                $app_id = $this->container->getAppManager()->getPackageApp('deskpro_ms_translator')->id;
            }

            $info = [
                'lang_codes'                   => $lang_codes,
                'lang_names'                   => $lang_names,
                'translate_ticket_message_url' => $this->generateUrl('agent_apps_run', ['app_id' => $app_id, 'action' => 'translate-ticket-message']),
                'translate_text_url'           => $this->generateUrl('agent_apps_run', ['app_id' => $app_id, 'action' => 'translate-text']),
            ];

            $js[] = 'window.DESKPRO_TRANSLATE_SERVICE = '.json_encode($info).';';
        }

        $date_formats = [
            'full'      => \Application\DeskPRO\Util::momentJsDateFormat(App::getSetting('core.date_full')),
            'fulltime'  => \Application\DeskPRO\Util::momentJsDateFormat(App::getSetting('core.date_fulltime')),
            'day'       => \Application\DeskPRO\Util::momentJsDateFormat(App::getSetting('core.date_day')),
            'day_short' => \Application\DeskPRO\Util::momentJsDateFormat(App::getSetting('core.date_day_short')),
            'time'      => \Application\DeskPRO\Util::momentJsDateFormat(App::getSetting('core.date_time')),
        ];
        $js[] = 'window.DESKPRO_DATE_FORMATS = '.json_encode($date_formats).';';

        $js[] = <<<JS
function Orb_Util_TimeAgo_getPhraseFor(type, num, ago)
{
    var phrasepre = 'reltime';
    if (ago) {
        phrasepre = 'reltimeago';
    }

    if ((type == 'secs' || type == 'sec') && num < 60) {
        var phrasename = 'agent.time.' + phrasepre + '_less_minute';
    } else {
        if (type == 'min') type = 'minute';
        else if (type == 'mins') type = 'minutes';
        else if (type == 'sec') type = 'second';
        else if (type == 'secs') type = 'seconds';

        var phrasename = 'agent.time.' + phrasepre + '_x_' + type;
        if (num == 1) {
            var phrasename = 'agent.time.' + phrasepre + '_1_' + type;
        }
        if (type == 'sec' && num <= 0) {
            var phrasename = 'agent.time.' + phrasepre + '_less_second';
        }
    }

    if (!window.DESKPRO_LANG || !window.DESKPRO_LANG[phrasename]) {
        console.warn("Missing phrase %s", phrasename);
    }

    return (window.DESKPRO_LANG && window.DESKPRO_LANG[phrasename] || "").replace(/\{0\}/g, num);
}
JS;

        $person = App::getCurrentPerson();
        if ($person && $person->getLanguage()) {
            $locale = $person->getLanguage()->getLocale();
        } else {
            $ls     = $this->container->getLanguageData();
            $locale = null;
            if ($defaultLanguage = $ls->getDefault()) {
                $locale = $defaultLanguage['locale'];
            }
        }

        $js[] = sprintf('window.DESKPRO_DEFAULT_LANG = "%s";', $locale);

        $js = implode("\n", $js);

        $response = $this->response;
        $response->headers->set('Content-Type', 'application/javascript');
        $response->setContent($js);

        return $response;
    }

    public function ajaxSavePrefsAction()
    {
        $prefsExpire = $this->in->getCleanValueArray('prefs_expire', 'raw', 'string');

        foreach ($this->in->getCleanValueArray('prefs', 'raw', 'string') as $prefName => $value) {
            $pref        = new Entity\PersonPref();
            $pref->name  = $prefName;
            $pref->value = $value;

            if (isset($prefsExpire[$prefName])) {
                try {
                    $date              = new \DateTime($prefsExpire[$prefName]);
                    $pref->date_expire = $date;
                } catch (\Exception $e) {
                }
            }

            App::getDb()->replace('people_prefs', [
                'person_id'   => $this->person->getId(),
                'name'        => $prefName,
                'date_expire' => $pref->date_expire ? $pref->date_expire->format('Y-m-d H:i:s') : null,
                'value_str'   => $pref->value_str,
                'value_array' => $pref->value_array ? serialize($pref->value_array) : null,
            ]);
        }

        return $this->createJsonResponse([
            'success' => true,
        ]);
    }

    public function proxyAction()
    {
        $url = $this->request->headers->get('X-DeskPRO-Proxy-Url');
        if ($url) {
            $used_req_url = false;
        } else {
            $url          = $this->in->getString('url');
            $used_req_url = true;
        }

        $urlinfo = @parse_url($url);
        if (!$url or !$urlinfo or empty($urlinfo['scheme']) or !preg_match('#^https?#', $urlinfo['scheme'])) {
            return $this->createResponse('Bad url', 400);
        }

        $originalMethod = $this->request->getMethod();
        $method         = $this->request->headers->get('X-DeskPRO-Proxy-Method');
        if (!$method) {
            $method = $originalMethod;
        }

        if ($originalMethod == 'GET') {
            $passData = $_GET;
            if ($used_req_url) {
                unset($passData['url']);
            }
        } else {
            $passData = file_get_contents('php://input');
        }

        switch (strtolower($method)) {
            case 'get':
                $method = 'GET';
                break;
            case 'post':
                $method = 'POST';
                break;
            case 'put':
                $method = 'PUT';
                break;
            case 'delete':
                $method = 'DELETE';
                break;
            default:
                $method = 'GET';
        }

        if ($method == 'GET' && is_array($passData) && $passData) {
            $url .= (strpos($url, '?') ? '&' : '?').http_build_query($passData);
        }

        $ch     = curl_init($url);
        $cainfo = CaBundle::getBundledCaBundlePath();
        if (file_exists($cainfo)) {
            @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            @curl_setopt($ch, CURLOPT_CAINFO, $cainfo);
        }
        @curl_setopt($ch, CURLOPT_CAINFO, $cainfo);
        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($passData) ? http_build_query($passData) : $passData);
        }

        if ($this->request->headers->get('X-DeskPRO-Proxy-Username') or $this->request->headers->get('X-DeskPRO-Proxy-Password')) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->request->headers->get('X-DeskPRO-Proxy-Username', '').':'.$this->request->headers->get('X-DeskPRO-Proxy-Password', ''));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        } else {
            $in_auth_type = $this->request->headers->get('X-DeskPRO-Proxy-Http-Auth', '');

            if ($in_auth_type) {
                $in_auth_type = strtolower($in_auth_type);
                $auth_type    = null;

                switch ($in_auth_type) {
                    case 'basic':
                        $auth_type = CURLAUTH_BASIC;
                        break;
                    case 'digest':
                        $auth_type = CURLAUTH_DIGEST;
                        break;
                    case 'gssnegotiate':
                        $auth_type = CURLAUTH_GSSNEGOTIATE;
                        break;
                    case 'ntlm':
                        $auth_type = CURLAUTH_NTLM;
                        break;
                    case 'any':
                        $auth_type = CURLAUTH_ANY;
                        break;
                    case 'safe':
                        $auth_type = CURLAUTH_ANYSAFE;
                        break;
                    default:
                        throw $this->createNotFoundException();
                }

                if ($auth_type) {
                    curl_setopt($ch, CURLOPT_HTTPAUTH, $auth_type);
                    curl_setopt($ch, CURLOPT_USERPWD, $this->request->headers->get('X-DeskPRO-Proxy-Auth-Credentials', ''));
                }
            }
        }

        $headers = [];
        if ($this->request->headers->get('X-DeskPRO-Proxy-Content-Type')) {
            $headers[] = 'Content-Type: '.$this->request->headers->get('X-DeskPRO-Proxy-Content-Type');
        } elseif (!empty($_SERVER['CONTENT_TYPE'])) {
            $headers[] = 'Content-Type: '.$_SERVER['CONTENT_TYPE'];
        }

        // Proxy custom headers
        foreach ($this->request->headers->all() as $name => $value) {
            $realname = Strings::extractRegexMatch('#^X\-DeskPRO\-Proxy\-Header\-(.*?)$#i', $name);
            if ($realname) {
                foreach ($value as $v) {
                    $headers[] = "$realname: ".$v;
                }
            }
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'DeskPRO AJAX Proxy');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        $contents = curl_exec($ch);
        $info     = curl_getinfo($ch);
        $err_no   = curl_errno($ch);
        $err_msg  = curl_error($ch);
        curl_close($ch);

        $response = $this->response;

        if ($err_no) {
            $response->setStatusCode(400);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode([
                'error' => $err_msg,
                'code'  => $err_no,
            ]));
        } else {
            if ($info['content_type']) {
                $response->headers->set('Content-Type', $info['content_type']);
            }
            if ($info['http_code']) {
                $response->setStatusCode($info['http_code']);
            }

            if ($contents) {
                $response->setContent($contents);
            } else {
                $response->setContent('');
            }
        }

        return $response;
    }

    public function ajaxLabelsAutocompleteAction($label_type)
    {
        $search    = $this->in->getString('term');
        $statement = $this->db->executeQuery('
            SELECT label
            FROM label_defs
            WHERE label_type = ? AND label LIKE ?
            ORDER BY label ASC
            LIMIT 50',
            [$label_type, '%'.$search.'%']);

        $array = [];

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $array[] = ['name' => $row['label'], 'value' => $row['label']];
        }

        return $this->createJsonResponse($array);
    }

    public function showBlobAction($blob_id)
    {
        $blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

        $response = $this->container->get('response');
        $response->headers->set('Content-Type', $blob['content_type'].'; filename='.$blob['filename']);
        $response->headers->set('Content-Length', $blob['filesize']);
        $response->headers->set('Content-Disposition', 'inline; filename='.$blob['filename']);

        $file = $this->container->getBlobStorage()->copyBlobRecordToString($blob);
        $response->setContent($file);

        return $response;
    }

    public function acceptTempUploadAction()
    {
        $copy_blobauth          = $this->in->getString('copy_blob');
        $allowedImageExtensions = ['gif', 'png', 'jpg', 'jpeg'];

        $props = [];
        if ($this->in->getString('tag')) {
            switch (trim($this->in->getString('tag'))) {
                case 'ticket_attachment':
                    $props['tag'] = 'ticket_attachment';
                    break;
            }
        }

        if ($copy_blobauth) {
            $blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthCode($copy_blobauth);
            if (!$blob) {
                $error               = [];
                $error['error_code'] = 'no_file';
                $error['error']      = $this->container->getTranslator()->phrase('agent.general.attach_error_no_file');

                return $this->createJsonResponse($error);
            }

            if ($this->in->getBool('is_image') && !$blob->isImage()) {
                $error = [
                    'error_code'   => 'not_in_allowed_exts',
                    'error_detail' => implode(',', $allowedImageExtensions),
                ];
                $error['error'] = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createJsonResponse($error);
            }

            $bs       = $this->container->getBlobStorage();
            $raw_file = $bs->copyBlobRecordToString($blob);

            $blob = $bs->createBlobRecordFromString($raw_file, $blob->filename, $blob->content_type, array_merge($props, ['is_temp' => true]));
            unset($raw_file);
        } else {
            $file   = $this->request->files->get('file-upload');
            $accept = $this->container->getAttachmentAccepter();

            $error = $accept->getError($file, 'agent');
            if (!$error && $this->in->getBool('is_image')) {
                $set = new \Application\DeskPRO\Attachments\RestrictionSet();
                $set->setAllowedExts($allowedImageExtensions);
                $accept->addRestrictionSet('only_images', $set);
                $error = $accept->getError($file, 'only_images');
            }

            if ($error) {
                $error['error'] = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createJsonResponse([$error]);
            }

            $blob = $accept->accept($file, true, $props);
        }

        if ($this->in->getString('attach_to_object')) {
            switch ($this->in->getString('attach_to_object')) {
                case 'article':
                    $article = $this->em->find('DeskPRO:Article', $this->in->getUint('object_id'));

                    $attach           = new \Application\DeskPRO\Entity\ArticleAttachment();
                    $attach['blob']   = $blob;
                    $attach['person'] = $this->person;

                    $article->addAttachment($attach);

                    $this->em->persist($attach);
                    $this->em->persist($article);
                    $this->em->flush();

                    break;

                case 'news':
                    $news = $this->em->find('DeskPRO:News', $this->in->getUint('object_id'));

                    $attach           = new \Application\DeskPRO\Entity\NewsAttachment();
                    $attach['blob']   = $blob;
                    $attach['person'] = $this->person;

                    $news->addAttachment($attach);

                    $this->em->persist($attach);
                    $this->em->persist($news);
                    $this->em->flush();

                    break;

                case 'feedback':
                    $feedback = $this->em->find('DeskPRO:Feedback', $this->in->getUint('object_id'));

                    $attach           = new \Application\DeskPRO\Entity\FeedbackAttachment();
                    $attach['blob']   = $blob;
                    $attach['person'] = $this->person;

                    $feedback->addAttachment($attach);
                    $this->em->persist($attach);
                    $this->em->persist($feedback);
                    $this->em->flush();

                    break;
            }
        }

        if ($this->in->getBool('save_media')) {
            $blob->is_media_upload = true;
            $this->em->persist($blob);
            $this->em->flush();
        }

        $res = $this->createJsonResponse([[
            'blob_id'           => $blob['id'],
            'blob_auth'         => $blob->authcode,
            'blob_auth_id'      => $blob->id.'-'.$blob->authcode,
            'download_url'      => $blob->getDownloadUrl(true, false),
            'filename'          => $blob['filename'],
            'filesize_readable' => $blob->getReadableFilesize(),
            'is_image'          => $blob->isImage(),
        ]]);

        // Required for iframe transport on IE to prevent 'download' popup
        $res->headers->set('Content-Type', 'text/plain');

        return $res;
    }

    public function acceptRedactorFileUploadAction()
    {
        return $this->acceptRedactorImageUploadAction(true);
    }

    public function acceptRedactorImageUploadAction($fileUpload = false)
    {
        $copy_blobauth = $this->in->getString('copy_blob');

        if ($copy_blobauth) {
            $blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthCode($copy_blobauth);
            if (!$blob) {
                $error               = [];
                $error['error_code'] = 'no_file';
                $error['error']      = $this->container->getTranslator()->phrase('agent.general.attach_error_no_file');

                return $this->createJsonResponse($error);
            }

            if (!$fileUpload && !$blob->isImage()) {
                $error = [
                    'error_code'   => 'not_in_allowed_exts',
                    'error_detail' => implode(',', ['gif', 'png', 'jpg', 'jpeg']),
                ];
                $error['error'] = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createJsonResponse($error);
            }

            $bs       = $this->container->getBlobStorage();
            $raw_file = $bs->copyBlobRecordToString($blob);

            $blob = $bs->createBlobRecordFromString($raw_file, $blob->filename, $blob->content_type, ['is_temp' => true]);
            unset($raw_file);
        } else {
            /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
            $file   = $this->request->files->get('file');
            $accept = $this->container->getAttachmentAccepter();

            $filename = $this->in->getString('filename');
            if ($filename) {
                // override filename
                $file = new \Symfony\Component\HttpFoundation\File\UploadedFile(
                    $file->getPathname(), $filename, $file->getClientMimeType(), $file->getClientSize(), $file->getError()
                );
            }

            $error = $accept->getError($file, 'agent');
            if (!$error) {
                $set  = new \Application\DeskPRO\Attachments\RestrictionSet();
                $exts = !$fileUpload
                    ? ['gif', 'png', 'jpg', 'jpeg']
                    : ['gif', 'png', 'jpg', 'jpeg', // also allow images
                        'pdf', 'doc', 'docx', 'xls', 'csv', 'xlsx', 'txt',
                       'rar', 'zip', 'tar.gz', '7zip', 'gzip', 'bzip',
                       'mp4', 'avi', 'wmv', 'mpeg', 'mov', '3gp', ];
                $set->setAllowedExts($exts);
                $accept->addRestrictionSet($fileUpload ? 'only_files' : 'only_images', $set);
                $error = $accept->getError($file, $fileUpload ? 'only_files' : 'only_images');
            }
            if ($error) {
                $error['error'] = $this->container->getTranslator()->phrase('agent.general.attach_error_'.$error['error_code'], $error);

                return $this->createJsonResponse($error);
            } else {
                $blob = $accept->accept($file, true);
            }
        }

        $blobResponse = [
            'blob_id'           => $blob['id'],
            'blob_auth'         => $blob->authcode,
            'blob_auth_id'      => $blob->id.'-'.$blob->authcode,
            'download_url'      => $blob->getDownloadUrl(true),
            'filename'          => $blob['filename'],
            'filesize_readable' => $blob->getReadableFilesize(),
            'is_image'          => $blob->isImage(),

            // needed for Redactor
            'filelink' => $blob->getDownloadUrl(true),
            'link'     => $blob->getDownloadUrl(true),
        ];

        if ($this->in->getBool('json')) {
            return $this->createJsonResponse(json_encode(['link' => $blob->getDownloadUrl(true)]));
        }

        return $this->render('AgentBundle:Misc:redactor-image-upload.html.twig', ['blob' => $blobResponse]);
    }

    public function redactorAutosaveAction($content_type, $content_id)
    {
        $inserted = false;

        // plain text message isnt really used, so dont want to spend a lot of
        // effort cleaning it, so just stripping html it on the off chance it's ever used in a template somehwere
        $message = Strings::stripTags(Strings::html2Text($this->in->getCleanValue('message', 'string', null, ['noclean' => true])));

        $extras = $this->in->getCleanValueArray('extras');
        $draft  = null;
        if ($message) {
            $message_html = trim($this->in->getHtml('message'));
            $message_html = Strings::prepareWysiwygHtml($message_html);

            $message_test = preg_replace('/<(p|div) class="dp-signature-start">(.*)$/s', '', $message_html);

            if ($message_test
                && (
                    !$this->person->getSignatureHtml()
                    ||
                    !Strings::compareHtml($message_test, $this->person->getSignatureHtml())
                )
            ) {
                $draft = $this->em->getRepository('DeskPRO:Draft')->insertDraft(
                    $content_type, $content_id, $message_html, $message_html, $extras
                );
                if ($draft) {
                    $inserted = $draft->id;
                }
            }
        }

        if (!$inserted) {
            $this->em->getRepository('DeskPRO:Draft')->deleteDraft($content_type, $content_id);
        }

        if ($inserted && $content_type == 'ticket') {
            $html = false;
            if ($draft) {
                $ticket = $this->em->getRepository('DeskPRO:Ticket')->find($content_id);
                if ($ticket) {
                    $html = $this->renderView('AgentBundle:Ticket:ticket-message-draft.html.twig', [
                        'draft'  => $draft,
                        'ticket' => $ticket,
                    ]);
                }
            }

            App::getContainer()
                ->get('event_dispatcher')
                ->dispatch(
                    TicketUpdatedEvent::EVENT_NAME,
                    new TicketUpdatedEvent(
                        'agent.ticket-draft-updated',
                        [
                            'ticket_id'  => $content_id,
                            'draft_html' => $html,
                            'via_person' => $this->person->getId(),
                        ]

                    )
                );
        }

        return $this->createJsonResponse([
            'inserted' => $inserted,
        ]);
    }

    public function parseVCardAction($blob_id = null)
    {
        if ($blob_id) {
            $blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

            $content = $this->container->getBlobStorage()->copyBlobRecordToString($blob);
        } else {
            $file = $this->request->files->get('files');

            $content = SafeFile::fileGetContents($file[0]->getPathName(), dirname($file[0]->getPathName()));
        }

        $fields = \Application\DeskPRO\Reader\VCard::parseVCard($content);

        //var_dump($fields); die;

        $res = $this->createJsonResponse([['fields' => $fields]]);

        // Required for iframe transport on IE to prevent 'download' popup
        $res->headers->set('Content-Type', 'text/plain');

        return $res;
    }

    /**
     * @param  $id
     */
    public function dismissHelpMessageAction($id)
    {
        $this->person->HelpMessages->dismiss($id);

        $this->createJsonResponse(['success' => true]);
    }

    /**
     * Set away status.
     *
     * @param  $status
     */
    public function setAgentStatusAction($status)
    {
        if (!$status or $status == 'away') {
            $status = 'away';
        } else {
            $status = 'available';
        }

        $sessionEnt                      = $this->session->getEntity();
        $sessionEnt['active_status']     = $status;
        $sessionEnt['is_chat_available'] = $this->in->getBool('is_chat_available');

        $this->session->set('is_chat_available', $this->in->getBool('is_chat_available'));
        $this->session->set('active_status', $status);
        $this->session->save();

        $this->person->setPreference('agent.chat.is_available', (int) $this->in->getBool('is_chat_available'));

        // Update status in all other active sessions
        $is_chat_avail     = (int) $this->in->getBool('is_chat_available');
        $is_chat_avail_old = (int) (!$this->in->getBool('is_chat_available'));

        // using REPLACE on the session data as a quick way to toggle the status in session data
        // without actually loading up the entire record
        $this->db->executeUpdate("
            UPDATE sessions
            SET is_chat_available = ?, active_status = ?, data = REPLACE(data, '\"is_chat_available\";i:$is_chat_avail_old;', '\"is_chat_available\";i:$is_chat_avail;')
            WHERE person_id = ? AND interface = ?
        ", [
                $this->in->getBool('is_chat_available'),
                $status,
                $this->person->getId(),
                'agent', ]
        );

        $this->em->transactional(function ($em) use ($sessionEnt) {
            $em->persist($sessionEnt);
            $em->flush();
        });

        AvailableTrigger::update();

        $this->container->get('event_dispatcher')->dispatch(
            AgentStatusChangedEvent::EVENT_NAME,
            new AgentStatusChangedEvent(
                'agent.ui.user-chat-status',
                $this->person->getId(),
                $this->in->getBool('is_chat_available')
            )
        );

        return $this->createJsonResponse(['success' => true, 'status' => $status]);
    }

    public function userInterfaceFrameAction()
    {
        return $this->render('AgentBundle:Misc:user-frame.html.twig');
    }

    public function redirectExternalAction($url)
    {
        if (!$this->container->getSetting('core.agent_intercept_external_link')) {
            $res = new \Symfony\Component\HttpFoundation\RedirectResponse($url, 302);

            return $res;
        }

        $urlinfo = parse_url($url);

        return $this->render('AgentBundle:Misc:redirect-external.html.twig', [
            'url'     => $url,
            'urlinfo' => $urlinfo,
        ]);
    }

    public function redirectExternalInfoAction($url)
    {
        return $this->createNotFoundException();
    }

    public function getPasswordConfirmCodeAction()
    {
        $password = $this->in->getString('password');

        $invalid_res = $this->createJsonResponse(['invalid' => true]);

        $code      = $this->session->getEntity()->generateSecurityToken('password_confirm'.$this->person->secret_string);
        $valid_res = $this->createJsonResponse(['code' => $code]);

        //------------------------------
        // Auth local
        //------------------------------

        $adapter = new \Application\DeskPRO\Auth\Adapter\Local(App::getOrm());
        $adapter->setCredentials($this->person->getPrimaryEmailAddress(), $password);
        $result = $adapter->authenticate();

        if ($result->isValid()) {
            return $valid_res;
        }

        //------------------------------
        // Auth usersources that accept local input
        //------------------------------

        $usersources = $this->em->getRepository(Usersource::class)->getLocalInputUsersources();
        foreach ($usersources as $us) {
            foreach ($this->person->getEmailAddresses() as $email) {
                /* @var $us \Application\DeskPRO\Entity\Usersource */
                $adapter = $this->_initUserSourceAdapter($us);
                $adapter->setFormData([
                    'username' => $email,
                    'password' => $password,
                ]);

                try {
                    $result = $adapter->authenticate();
                } catch (\Exception $e) {
                    continue;
                }

                if ($result->isValid()) {
                    return $valid_res;
                }
            }
        }

        return $invalid_res;
    }

    protected function _initUserSourceAdapter($usersource, $context = null)
    {
        $adapter = $usersource->getAdapter()->getAuthAdapter();

        if ($adapter instanceof \Orb\Auth\Adapter\FormLoginInterface) {
            $adapter->setFormData($_POST);
        }

        if ($context && $adapter instanceof \Orb\Auth\Adapter\DisplayContextInterface) {
            $adapter->setDisplayContext($context);
        }

        if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
            $adapter->setCallbackUrl(
                rtrim($this->container->getSetting('core.deskpro_url'), '/').
                $this->generateUrl('user_login_callback', ['usersource_id' => $usersource['id']], UrlGenerator::RELATIVE_PATH)
            );
        }

        if ($adapter instanceof \Orb\Auth\Adapter\SessionStateInterface) {
            $auth_state = new \Orb\Auth\StateHandler\ArrayAccessWrapper($this->session);
            $auth_state->setClearStateMethod('clear');

            $adapter->setStateHandler($auth_state);
        }

        return $adapter;
    }

    public function submitDeskproFeedbackAction()
    {
        return $this->createJsonResponse(['success' => true]);
    }

    public function getServerTimeAction()
    {
        $d = \Orb\Util\Dates::makeUtcDateTime($this->person->getDateTime());

        return $this->createJsonResponse([
            'timestamp_utc'  => time(),
            'timestamp'      => $d->getTimestamp(),
            'time_formatted' => $d->format('g:i a'),
            'time_hour'      => (int) $d->format('H'),
            'time_minute'    => (int) $d->format('i'),
        ]);
    }

    public function getRequirejsLoaderAction()
    {
        $app_perms = App\AgentAppPermissions::newFromDb($this->container->getDb(), $this->container->getAppManager()->getAllApps());
        $manager   = $this->container->getAppManager()->getScopeFilter('agent', null, $app_perms->getAgentAppFilterCallable($this->person));

        $rjs = new RequireJsConfigGenerator();
        $rjs->setBaseUrlExpr('ASSETS_BASE_URL');

        if ($this->container->isDebug()) {
            $rjs->setUrlArgsExpr('"v=" + (new Date()).getTime()');
        } elseif (defined('DP_BUILD_TIME')) {
            $rjs->setUrlArgsExpr('"v='.DP_BUILD_TIME.'"');
        }

        $rjs->addPath('AppPlatform', 'app-build/Agent/AppPlatform/Platform');
        $rjs->addPath('AppPlatformConfig', str_replace('.js', '', $this->generateUrl('agent_apps_config_js')));
        $rjs->addPath('AgentApp', 'app-build/Agent/App/AgentModule');

        $rjs_apps = new AppsRequireJsConfigGenerator($manager, $this->generateUrl('serve_file_root').'/apps');
        $rjs->addPathsFromGenerator($rjs_apps);

        $rjs_config = $rjs->generateRequireJsConfigCode();

        $js = $rjs_config;

        $response = $this->response;
        $response->headers->set('Content-Type', 'application/javascript');
        $response->setContent($js);

        return $response;
    }

    public function getAppsConfigAction()
    {
        $js = [];

        $app_perms = App\AgentAppPermissions::newFromDb($this->container->getDb(), $this->container->getAppManager()->getAllApps());
        $manager   = $this->container->getAppManager()->getScopeFilter('agent', null, $app_perms->getAgentAppFilterCallable($this->person));

        foreach ($manager->getAllApps() as $app) {
            $package  = $app->package;
            $appAsset = $package->getTaggedAsset('app_js');
            $name     = "{$package->name}/app";

            if ($appAsset) {
                $class_name = $name;
            } else {
                $class_name = 'Agent/AppPlatform/Context/AppContext';
            }

            if ($package->native_name) {
                $native_baseurl = $this->generateUrl('serve_file_root').'/apps/'.$package->native_name;
            } else {
                $native_baseurl = null;
            }

            $asset_files = [];
            foreach (['html', 'res'] as $asset_type) {
                foreach ($package->getTaggedAssets($asset_type) as $asset) {
                    $asset_id = $package->name."/$asset_type/".$asset->name;

                    if ($native_baseurl) {
                        $asset_path = $native_baseurl."/$asset_type/".$asset->name;
                    } else {
                        $asset_path = $asset->blob->getDownloadUrl(false, false);
                    }

                    $asset_files[$asset_id] = $asset_path;
                }
            }

            if ($asset_files) {
                $asset_files_js = [];
                foreach ($asset_files as $k => $v) {
                    $asset_files_js[] = "\t\t\t\"$k\": \"$v\"";
                }
                $asset_files_js = "{\n".implode(",\n", $asset_files_js)."\n\t\t}";
            } else {
                $asset_files_js = '{}';
            }

            $module_asset = $package->getTaggedAsset('module_js');

            $infoJson = '';
            $infoJson .= "\t\t\"id\": {$app->id},\n";
            $infoJson .= "\t\t\"packageName\": \"{$package->name}\",\n";
            $infoJson .= "\t\t\"moduleName\": ".($module_asset ? "\"{$package->name}/module\"" : 'null').",\n";
            $infoJson .= "\t\t\"contextName\": \"$class_name\",\n";
            $infoJson .= "\t\t\"scope\": \"agent\",\n";
            $infoJson .= "\t\t\"settings\": ".json_encode($app->getOutputSettings(), JSON_FORCE_OBJECT).",\n";
            $infoJson .= "\t\t\"assets\": $asset_files_js\n";

            $infoJson = trim($infoJson);

            $js_row = "\t// {$package->name} :: App[{$app->id}]\n";
            $js_row .= "\tapps.push({\n\t\t$infoJson\n\t});";
            $js[] = $js_row;
        }

        array_unshift($js, "define(function() {\n\tvar apps = [];");

        $js[] = "\treturn apps;\n});\n";

        $js = implode("\n\n", $js);

        $response = $this->response;
        $response->headers->set('Content-Type', 'application/javascript');
        $response->setContent($js);

        return $response;
    }

    public function getMyInfoAction()
    {
        $agent = $this->person;

        $serializer = $this->getContainer()->getSystemService('serializer');
        $agent_data = $serializer->serialize($agent);

        $agent_data['teams'] = [];

        $agent->loadHelper('Agent');
        $agent->loadHelper('AgentTeam');
        $agent->loadHelper('AgentPermissions');
        $agent->loadHelper('PermissionsManager');

        foreach ($this->container->getAgentData()->getTeamsByIds($agent->getHelper('AgentTeam')->getAgentTeamIds()) as $t) {
            $agent_data['teams'][] = $t->toApiData();
        }

        $perm_loader = new AgentPermsPersonDbLoader($agent, $this->em);

        $data = [
            'agent' => $agent_data,
            'perms' => $perm_loader->getEffectivePermissions()->toArray(),
        ];

        if ($this->in->getBool('extended')) {
            $data['signature_html'] = $agent->getSignatureHtml();
        }

        return $this->createJsonResponse($data);
    }

    public function dismissDpNewsAction($id)
    {
        $dp_news = require_once DP_ROOT.'/sys/config/config.news.php';
        if (!isset($dp_news[$id])) {
            throw $this->createNotFoundException();
        }

        $read_news   = $this->person->getPref('agent.ui.dp_news', []);
        $read_news[] = $id;
        $p           = $this->person->setPreference('agent.ui.dp_news', $read_news);
        $this->em->persist($p);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    public function viewDpNewsAction($id)
    {
        $dp_news = require_once DP_ROOT.'/sys/config/config.news.php';
        if (!isset($dp_news[$id])) {
            throw $this->createNotFoundException();
        }

        return $this->render('AgentBundle:Misc:dp-news-view.html.twig', [
            'dp_news' => $dp_news[$id],
        ]);
    }
}
