<?php

/**
 * DeskPRO.
 *
 * @category Templating
 */

namespace Application\DeskPRO\Twig\Extension;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Topic;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\HttpFoundation\Session;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Application\DeskPRO\Usersource\UsersourceManager;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use DeskPRO\Bundle\AppBundle\Routing\RouterUtils;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Twig\TwigTemplateRenderer;
use DeskPRO\Component\Filesystem\SafeFile;
use DeskPRO\Component\Util\RegexUtils;
use DpSys\CodePlugin\DpPlugins;
use DpSys\License;
use Orb\Auth\Adapter\IframeSsoInterface;
use Orb\Auth\Adapter\JsSsoInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Orb\Data\Countries;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class TemplatingExtension extends \Twig_Extension
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;
    /** @var array */
    protected $counter_registry;

    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    public function getBrandStack()
    {
        return $this->container->getBrandStack();
    }

    /**
     * @return \Application\DeskPRO\Templating\Engine
     */
    public function getTemplating()
    {
        return $this->container->get('templating');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('constant', [$this, 'getConstant'], []),
            new \Twig_SimpleFunction('phrase_code', [$this, 'getPhraseText'], []),
            new \Twig_SimpleFunction('has_phrase', [$this, 'hasPhrase'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('phrase_object', [$this, 'getPhraseObject']),
            new \Twig_SimpleFunction('phrase_dev', [$this, 'getPhraseDev']),
            new \Twig_SimpleFunction('language_html_attr', [$this, 'getLanguageHtmlAttributes'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('language_arrow', [$this, 'getLanguageArrow'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('is_rtl', [$this, 'isRtl']),
            new \Twig_SimpleFunction('url_fragment', [$this, 'urlFragment']),
            new \Twig_SimpleFunction('asset_full', [$this, 'assetFull']),
            new \Twig_SimpleFunction('asset_url', [$this, 'assetFull']),
            new \Twig_SimpleFunction('url_full', [$this, 'urlFull']),
            new \Twig_SimpleFunction('url_display', [$this, 'urlDisplay']),
            new \Twig_SimpleFunction('helpdesk_url', [$this, 'helpdeskUrl']),
            new \Twig_SimpleFunction('brand_setting', [$this, 'getBrandSetting'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('cross_brand_setting', [$this, 'getCrossBrandSetting'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('is_helpdesk_path', [$this, 'isHelpdeskPath']),
            new \Twig_SimpleFunction('deskpro_debug', [$this, 'isDebugMode']),
            new \Twig_SimpleFunction('render_custom_field', [$this, 'renderCustomField'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_custom_field_text', [$this, 'renderCustomFieldText']),
            new \Twig_SimpleFunction('render_custom_field_form', [$this, 'renderCustomFieldForm'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('el_uid', [$this, 'elUid'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('rand', [$this, 'rand'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('str_repeat', [$this, 'strRepeat']),
            new \Twig_SimpleFunction('flash_message', [$this, 'flashMessage']),
            new \Twig_SimpleFunction('compare_type', [$this, 'compareType']),
            new \Twig_SimpleFunction('object_path', [$this, 'getObjectPath']),
            new \Twig_SimpleFunction('object_path_agent', [$this, 'getObjectPathAgent']),
            new \Twig_SimpleFunction('get_type', [$this, 'getType']),
            new \Twig_SimpleFunction('debug_var', [$this, 'debugVar']),
            new \Twig_SimpleFunction('security_token', [$this, 'securityToken']),
            new \Twig_SimpleFunction('static_security_token', [$this, 'staticSecurityToken']),
            new \Twig_SimpleFunction('static_security_token_secret', [$this, 'staticSecurityTokenSecret']),
            new \Twig_SimpleFunction('render_usersource', [$this, 'renderUsersource'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_data', [$this, 'getData']),
            new \Twig_SimpleFunction('dp_asset', [$this, 'getAssetic']),
            new \Twig_SimpleFunction('dp_asset_raw', [$this, 'getAsseticRaw']),
            new \Twig_SimpleFunction('dp_asset_html', [$this, 'htmlGetAssetic'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('start_counter', [$this, 'startCounter']),
            new \Twig_SimpleFunction('get_counter', [$this, 'getCounter']),
            new \Twig_SimpleFunction('inc_counter', [$this, 'incCounter']),
            new \Twig_SimpleFunction('form_token', [$this, 'formToken'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('relative_time', [$this, 'relativeTime'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_service_url', [$this, 'getServiceUrl'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_service_url_raw', [$this, 'getServiceUrlRaw'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_instance_ability', [$this, 'getInstanceAbility']),
            new \Twig_SimpleFunction('is_array', [$this, 'isArray']),
            new \Twig_SimpleFunction('gravatar_for_email', [$this, 'gravatar']),
            new \Twig_SimpleFunction('time_group_phrase', [$this, 'getTimeGroupPhrase']),
            new \Twig_SimpleFunction('captcha_html', [$this, 'captchaHtml'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('include_file', [$this, 'includeFile'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('include_php_file', [$this, 'includePhpFile'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('include_code_plugin', [$this, 'includeCodePlugin'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('var_dump', [$this, 'dumpVar']),
            new \Twig_SimpleFunction('dp_copyright', [$this, 'staticGetUserCopyrightHtml'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dp_widgets', [$this, 'getWidgets'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dp_widgets_raw', [$this, 'getWidgetsRaw']),
            new \Twig_SimpleFunction('dp_widget_id', [$this, 'getWidgetHtmlId']),
            new \Twig_SimpleFunction('dp_widget_tabs_header', [$this, 'getWidgetTabsHeader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dp_app_loc', [$this, 'getDpAppLocation'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dp_widget_tabs', [$this, 'getWidgetTabsBody'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dp_js_sso_loader', [$this, 'getJsSsoLoader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dp_js_sso_share', [$this, 'getJsSsoShare'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('base_template_name', [$this, 'getBaseTemplateName'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('array_attr', [$this, 'getArrayAttribute']),
            new \Twig_SimpleFunction('min', [$this, 'min']),
            new \Twig_SimpleFunction('max', [$this, 'max']),
            new \Twig_SimpleFunction('match', [$this, 'match']),
            new \Twig_SimpleFunction('set_tplvar', [$this, 'set_tplvar'], ['is_safe' => ['html'], 'needs_context' => true]),
            new \Twig_SimpleFunction('tpl_source', [$this, 'getTplSourceTemplate'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('ng_plural_phrase', [$this, 'ngPluralPhrase'], []),
            new \Twig_SimpleFunction('ng_href', [$this, 'ngHref'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('ng_href_var', [$this, 'ngHrefVar'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('server_capable', [$this, 'serverCapable'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('ng_var', [$this, 'ngVar'], []),
            new \Twig_SimpleFunction('ng_bind', [$this, 'ngBind'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('ng_static_var', [$this, 'ngStaticVar'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('ng_tpl', [$this, 'ngIncTpl'], ['is_safe' => ['html'], 'needs_context' => true]),
            new \Twig_SimpleFunction('js_error_tracking', [$this, 'js_error_tracking'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('isChatAvailable', [$this->container->get('brand_aware_settings_resolver'), 'isChatAvailable']),
            new \Twig_SimpleFunction('calcGroupedHierarchyCount', [$this, 'calcGroupedHierarchyCount']),

            // override so we can suppress errors where templates are out of date
            new \Twig_SimpleFunction('url', [$this, 'getUrl']),
            new \Twig_SimpleFunction('has_login_form', [$this, 'hasLoginForm'], []),
            new \Twig_SimpleFunction('get_currency', [$this, 'getCurrency'], []),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('safe_link_urls', [$this, 'safeLinkUrls'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('safe_link_urls_html', [$this, 'safeLinkUrlsHtml'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('link_agent_short_code_html', [$this, 'linkAgentShortCodeHtml'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('raw_url_encode', [$this, 'rawUrlEncode'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('repeat', [$this, 'strRepeat']),
            new \Twig_SimpleFilter('trim', [$this, 'strTrim']),
            new \Twig_SimpleFilter('ltrim', [$this, 'strLtrim']),
            new \Twig_SimpleFilter('rtrim', [$this, 'strRtrim']),
            new \Twig_SimpleFilter('encode_number', [$this, 'encNum'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('decode_number', [$this, 'decNum'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('md5_hash', [$this, 'getMd5'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('to_jqueryui_dateformat', [$this, 'jqueryUiDateFormat']),
            new \Twig_SimpleFilter('time_length', [$this, 'timeLength']),
            new \Twig_SimpleFilter('momentjs_format', [$this, 'momentJsFormat']),
            new \Twig_SimpleFilter('slugify', [$this, 'slugify']),
            new \Twig_SimpleFilter('emphasize_words', [$this, 'emphasizeWords'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('strip_linebreaks', [$this, 'stripLinebreaks']),
            new \Twig_SimpleFilter('explode', [$this, 'explodeString']),
            new \Twig_SimpleFilter('split', [$this, 'explodeString']),
            new \Twig_SimpleFilter('join', [$this, 'implodeArray']),
            new \Twig_SimpleFilter('implode', [$this, 'implodeArray']),
            new \Twig_SimpleFilter('crc32', [$this, 'crc32']),
            new \Twig_SimpleFilter('url_domain', [$this, 'getUrlDomain']),
            new \Twig_SimpleFilter('truncate', [$this, 'strTruncate']),
            new \Twig_SimpleFilter('first', [$this, 'getFirst']),
            new \Twig_SimpleFilter('last', [$this, 'getLast']),
            new \Twig_SimpleFilter('filesize_display', [$this, 'filesizeDisplay']),
            new \Twig_SimpleFilter('url_trim_scheme', [$this, 'urlTrimScheme']),
            new \Twig_SimpleFilter('country_name', [$this, 'countryName']),
            new \Twig_SimpleFilter('count_lines', [$this, 'countLines']),
            new \Twig_SimpleFilter('smart_wrap', [$this, 'smartWrap']),
            new \Twig_SimpleFilter('json_encode_inhtml', [$this, 'jsonEncodeInHtml'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('strip_html', [$this, 'stripHtml']),
            new \Twig_SimpleFilter('text_wrap_marks', [$this, 'textWrapMarks']),
            new \Twig_SimpleFilter('regex_replace', [$this, 'regexReplace']),
            new \Twig_SimpleFilter('hex2rgb', [$this, 'hex2rgb']),
            new \Twig_SimpleFilter('trans', [$this, 'dummy']),
            new \Twig_SimpleFilter('transchoice', [$this, 'dummy']),
            new \Twig_SimpleFilter('plain_template_filter', [$this, 'plain_template_filter']),
            new \Twig_SimpleFilter('content', [$this, 'replaceContent']),
            new \Twig_SimpleFilter('content_pdf', [$this, 'replaceContentPdf']),

            // Override for custom UTF-8 handling
            new \Twig_SimpleFilter('upper', [$this, 'strUpper']),
            new \Twig_SimpleFilter('lower', [$this, 'strLower']),
        ];
    }

    public function getPath($name, $parameters = [])
    {
        try {
            return $this->container->getRouter()->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (\Exception $e) {
            if ($this->container->isDebug()) {
                throw $e;
            }

            return '';
        }
    }

    public function getUrl($name, $parameters = [])
    {
        try {
            return $this->container->getRouter()->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (\Exception $e) {
            if ($this->container->isDebug()) {
                throw $e;
            }

            return '';
        }
    }

    public function getBaseTemplateName($name)
    {
        $parts = explode(':', $name);
        $name  = array_pop($parts);
        $name  = str_replace('.html.twig', '', $name);

        return $name;
    }

    public function getConstant($name = '')
    {
        static $whitelist = [
            'DP_BUILD_NUM'            => true,
            'DP_BUILD_TIME'           => true,
            'DPC_SITE_ID'             => true,
            'DPC_SITE_DOMAIN'         => true,
            'DPC_SITE_DOMAIN_ALT'     => true,
            'DPC_SITE_BUILD_NUM'      => true,
            'DPC_ACCOUNT_ID'          => true,
            'DPC_BILL_OVERDUE'        => true,
            'DPC_BILL_DATE'           => true,
            'DP_NOTIFY_LOGIN_SCRIPT'  => true,
            'DP_NOTIFY_LOGOUT_SCRIPT' => true,
        ];

        if (!$name || !defined($name) || !isset($whitelist[$name])) {
            return '';
        }

        return constant($name);
    }

    public function filesizeDisplay($size)
    {
        if ($size < 0) {
            return 'n/a';
        }

        return \Orb\Util\Numbers::filesizeDisplay($size);
    }

    public function getTimeGroupPhrase($time)
    {
        static $time_phrases = [
            300      => '< 5 minutes',
            900      => '5 - 15 minutes',
            1800     => '15 - 30 minutes',
            3600     => '30 - 60 minutes',
            7200     => '1 - 2 hours',
            10800    => '2 - 3 hours',
            14400    => '3 - 4 hours',
            21600    => '4 - 6 hours',
            43200    => '6 - 12 hours',
            86400    => '12 - 24 hours',
            172800   => '1 - 2 days',
            259200   => '2 - 3 days',
            345600   => '3 - 4 days',
            432000   => '4 - 5 days',
            518400   => '5 - 6 days',
            604800   => '6 - 7 days',
            1209600  => '1 - 2 weeks',
            1814400  => '2 - 3 weeks',
            2419200  => '3 - 4 weeks',
            4838400  => '1 - 2 months',
            7257600  => '2 - 3 months',
            9676800  => '3 - 4 months',
            12096000 => '4 - 5 months',
            14515200 => '5 - 6 months',
        ];

        foreach ($time_phrases as $min => $phrase) {
            if ($time <= $min) {
                return $phrase;
            }
        }

        return '> 6 months';
    }

    public function gravatar($email, $size = 80)
    {
        $hash = strtolower(md5($email));
        $url  = 'http://www.gravatar.com/avatar/'.$hash.'?';
        $url .= '&d='.$this->container->getRouter()->generate('serve_default_picture', ['s' => $size], UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }

    public function getFirst($var)
    {
        if (!$var) {
            return;
        }

        return \Orb\Util\Arrays::getFirstItem($var);
    }

    public function getLast($var)
    {
        if (!$var) {
            return;
        }

        return \Orb\Util\Arrays::getLastItem($var);
    }

    public function isArray($var)
    {
        return is_array($var);
    }

    public function getInstanceAbility($method)
    {
        $method = Strings::underscoreToCamelCase($method);

        return $this->container->getSystemService('instance_ability')->$method();
    }

    public function getServiceUrl($name, $params = null, $named_params = null, $html = true)
    {
        if (!$params || !is_array($params)) {
            $params = null;
        }
        if (!$named_params || !is_array($named_params)) {
            $params = null;
        }

        return $this->container->get('deskpro.service_urls')->get($name, $params, $named_params, $html);
    }

    public function getServiceUrlRaw($name, $params = null, $named_params = null)
    {
        return $this->getServiceUrl($name, $params, $named_params, false);
    }

    public function relativeTime($secs, $detail = 2)
    {
        $prefix = 'user.time.';
        if (DP_INTERFACE == 'admin' || DP_INTERFACE == 'agent') {
            $prefix = 'agent.time.';
        }

        return $this->container->get('deskpro.core.translate')->secsToReadable($secs, $detail, $prefix);
    }

    public function strTruncate($str, $width = 80, $dots = true)
    {
        if (strlen($str) <= $width) {
            return $str;
        }

        if ($dots) {
            if ($dots === true) {
                $dots = '...';
            }

            return trim(substr($str, 0, $width).$dots);
        } else {
            return trim(substr($str, 0, $width));
        }
    }

    public function startCounter($name = 'default', $start = 1)
    {
        $this->counter_registry[$name] = 1;

        return '';
    }

    public function getCounter($name = 'default')
    {
        return isset($this->counter_registry[$name]) ? $this->counter_registry[$name] : 0;
    }

    public function incCounter($name = 'default')
    {
        if (!isset($this->counter_registry[$name])) {
            $this->counter_registry[$name] = 0;
        }

        $v = $this->counter_registry[$name]++;

        return $v;
    }

    public function getUrlDomain($string)
    {
        $urlinfo = @parse_url($string);
        if (!$urlinfo) {
            return $string;
        }

        return @$urlinfo['host'];
    }

    public function crc32($string)
    {
        $string = (string) $string;

        return sprintf('%u', crc32($string));
    }

    public function safeLinkUrlsHtml($text)
    {
        return Strings::linkifyHtml($text, true);
    }

    public function safeLinkUrls($text)
    {
        $text = htmlspecialchars($text);

        return Strings::linkifyHtml($text, true);
    }

    public function linkAgentShortCodeHtml($html)
    {
        $id_map = [
            't'  => ['Ticket', 'agent/#app.tickets,t.o:'],
            'p'  => ['Person', 'agent/#app.people,p.o:'],
            'o'  => ['Organization', 'agent/#app.people.orgs,o.o:'],
            'a'  => ['Article', 'agent/#app.publish,a.o:'],
            'n'  => ['News', 'agent/#app.publish,n.o:'],
            'd'  => ['Download', 'agent/#app.publish,d.o:'],
            'i'  => ['Feedback', 'agent/#app.feedback,i.o:'],
            'tw' => ['Tweet', 'agent/#app.twitter,tw.o:'],
        ];

        $url = $this->container->getBrandSetting('core.deskpro_url');

        foreach ($id_map as $prefix => $info) {
            $html = preg_replace(
                '/\{\{\s*'.$prefix.'-(\d+)\s*\}\}/',
                '<a href="'.$url.$info[1].'$1">'.$info[0].' #$1</a>',
                $html
            );
        }

        return $html;
    }

    public function getAssetic($name)
    {
        $assetic_manager = $this->container->getSystemService('assetic_manager');

        return $assetic_manager->getUrl($name);
    }

    public function getAsseticRaw($name)
    {
        $assetic_manager = $this->container->getSystemService('assetic_manager');

        return $assetic_manager->getRawUrls($name);
    }

    public function implodeArray($array, $sep = ', ')
    {
        if (!$array || !is_array($array)) {
            return '';
        }

        return implode($array, $sep);
    }

    public function explodeString($string, $del = ',')
    {
        $ret    = [];
        $string = (string) $string;

        foreach (explode($del, $string) as $p) {
            $ret[] = trim($p);
        }

        return $ret;
    }

    public function stripHtml($str)
    {
        return Strings::html2Text($str);
    }

    public function stripLinebreaks($str)
    {
        $str = str_replace(["\r\n", "\n"], ' ', $str);
        $str = str_replace(['<br />', '<br/>', '<br>'], ' ', $str);
        $str = str_replace(['<p>', '</p>', '<p />', '<p/>'], ' ', $str);

        return $str;
    }

    public function htmlGetAssetic($name, $options = [])
    {
        $raw_packs = $this->container->get('deskpro.app_env')->getConfig('settings.raw_assets')
            ?: $this->container->get('deskpro.app_env')->getConfig('paths.raw_assets')
            ?: [];
        $use_less             = App::getConfig('debug.less_use_less', false);
        $disable_client_cache = App::getConfig('debug.disable_client_cache', false);

        if (App::getConfig('debug.dev') && !$raw_packs) {
            $raw_packs = ['all'];
        }

        if ($raw_packs && (in_array($name, $raw_packs) or in_array('all', $raw_packs) or (in_array('all -vendors', $raw_packs) && $name != 'agent_vendors'))) {
            $urls = $this->getAsseticRaw($name);
        } else {
            $urls = [$this->getAssetic($name)];
        }

        $qs_append = ($disable_client_cache ? time() : DP_BUILD_TIME);

        if (App::getConfig('asset_version_id')) {
            $qs_append = App::getConfig('asset_version_id');
        }

        $html = [];

        foreach ($urls as $url) {
            $type = Strings::getExtension($url);

            $url .= '?'.$qs_append;

            switch ($type) {
                case 'js':
                    $html[] = '<script type="text/javascript" src="'.$url.'"></script>';
                    break;
                case 'css':
                    if (!isset($options['media'])) {
                        $options['media'] = 'screen,print';
                    }
                    $html[] = '<link rel="stylesheet" type="text/css" media="'.$options['media'].'" href="'.$url.'" />';
                    break;
                case 'less':
                    if (!isset($options['media'])) {
                        $options['media'] = 'screen,print';
                    }

                    if (!$use_less && strpos($url, '/stylesheets-less/') !== false) {
                        $url    = str_replace('/stylesheets-less/', '/stylesheets/', $url);
                        $url    = str_replace('.less', '.css', $url);
                        $html[] = '<link rel="stylesheet" type="text/css" media="'.$options['media'].'" href="'.$url.'" />';
                    } else {
                        $html[] = '<link rel="stylesheet/less" type="text/css" media="'.$options['media'].'" href="'.$url.'" />';
                    }
                    break;
            }
        }

        return implode("\n", $html);
    }

    public function getData($id)
    {
        switch ($id) {
            case 'country_names':
                return \Orb\Data\Countries::getCountryNames();
                break;
            case 'countries':
                return \Orb\Data\Countries::getCountryArray();
                break;
            case 'us_states':
                return \Orb\Data\Countries::getUsStates();
                break;
            case 'timezones':
                $tzs = \DateTimeZone::listIdentifiers();
                $tzs = array_combine($tzs, $tzs);

                foreach ($tzs as &$tz_name) {
                    $tz_name = str_replace('/', ' ▸ ', $tz_name);
                    $tz_name = str_replace('_', ' ', $tz_name);
                }

                return $tzs;
                break;
            default:
                return;
        }
    }

    public function emphasizeWords($string, $words)
    {
        if (!is_array($words)) {
            $words = Strings::splitWords($words);
        }

        if (!$words) {
            return $string;
        }

        $string = htmlspecialchars($string);
        foreach ($words as $w) {
            $w      = htmlspecialchars($w);
            $string = RegexUtils::safePregReplace('#(\\b)('.preg_quote($w, '#').')(\\b)#iu', '$1<em>$2</em>$3', $string);
        }

        return $string;
    }

    /**
     * @param Usersource $usersource
     * @param string     $type
     * @param array      $params
     *
     * @return string
     */
    public function renderUsersource(Usersource $usersource, $type, array $params = [])
    {
        if ($type === 'social_login_btn') {
            return $this->renderSocialLoginButton($usersource, $params);
        }

        return $this->renderDefaultUserSource($usersource, $type, $params);
    }

    private function renderSocialLoginButton(Usersource $usersource, array $params = [])
    {
        $providers        = (array) $usersource->getOption('providers');
        $enabledProviders = array_filter(
            array_keys($providers),
            function ($provider) use ($providers) {
                return $providers[$provider];
            }
        );

        if (empty($enabledProviders)) {
            return '';
        }

        // add params
        $params['usersource'] = $usersource;
        if (!isset($params['type'])) {
            $params['type'] = 'user';
        }

        $params['providers'] = [];
        foreach ($enabledProviders as $provider) {
            $params['providers'][] = ['name' => $provider, 'url' => '/'];
        }

        $tpl  = 'DeskPRO:Auth:'.'social-login'.'.html.twig';
        $html = $this->getTemplating()->render($tpl, $params);

        return $html;
    }

    /**
     * @param Usersource $usersource
     * @param string     $type
     * @param array      $params
     *
     * @return string
     */
    private function renderDefaultUserSource(Usersource $usersource, $type, array $params = [])
    {
        // clean up params
        $params['usersource'] = $usersource;
        if (!isset($params['type'])) {
            $params['type'] = 'user';
        }

        // get template name
        $name = $usersource->getAdapter()->getTypename();
        $tpl  = 'DeskPRO:Auth:'.$name.'-'.$type.'.html.twig';

        $html = $this->getTemplating()->render($tpl, $params);

        return $html;
    }

    public function slugify($str)
    {
        return Strings::slugifyTitle($str);
    }

    public function jqueryUiDateFormat($format)
    {
        // Map of PHP symbols to jQuery date format symbols
        static $php_sym = [
            'd' => 'dd', 'D' => 'D', 'j' => 'd', 'l' => 'DD',
            'N' => '', 'S' => '', 'w' => '', 'z' => 'o',
            'W' => '',
            'F' => 'MM', 'm' => 'mm', 'M' => 'M', 'n' => 'm',
            't' => '',
            'L' => '', 'o' => '', 'Y' => 'yy', 'y' => 'y',
            'a' => '', 'A' => '', 'B' => '', 'g' => '',
            'G' => '', 'h' => '', 'H' => '', 'i' => '',
            's' => '', 'u' => '',
        ];

        $format_len = strlen($format);
        $new_format = [];
        $escaping   = false;

        for ($i = 0; $i < $format_len; ++$i) {
            $char = $format[$i];
            if ($char === '\\') {
                ++$i;
                if ($escaping) {
                    $new_format[] = $format[$i];
                } else {
                    $new_format[] = '\''.$format[$i];
                }
                $escaping = true;
            } else {
                if ($escaping) {
                    $new_format[] = "'";
                    $escaping     = false;
                }
                if (isset($php_sym[$char])) {
                    $new_format[] = $php_sym[$char];
                } else {
                    $new_format[] = $char;
                }
            }
        }

        return implode('', $new_format);
    }

    public function timeLength($length, $max_unit = null, $as_html = false)
    {
        return \Application\DeskPRO\Util::getPrintableTimeLength($length, $max_unit, $as_html);
    }

    public function momentJsFormat($format)
    {
        switch ($format) {
            case 'full':
                //D, jS M Y
                $format = $this->container->getSetting('core.date_full');
                break;

            case 'fulltime':
                //D, jS M Y g:ia
                $format = $this->container->getSetting('core.date_fulltime');
                break;

            case 'day':
                //M j Y
                $format = $this->container->getSetting('core.date_day');
                break;

            case 'day_short':
                //M j
                $format = $this->container->getSetting('core.date_day_short');
                break;

            case 'time':
                //g:i a
                $format = $this->container->getSetting('core.date_time');
                break;
        }

        return \Application\DeskPRO\Util::momentJsDateFormat($format);
    }

    public function formToken($name = '', $field_name = '_dp_security_token')
    {
        if (!$this->container->getSession()->getEntity()->getPersonId()) {
            $html = "<!--DP_FORM_TOKEN($name, $field_name)-->";
        } else {
            $html = '';
        }

        $html .= '<input type="hidden" name="'.$field_name.'" value="'.$this->container->getSession()
                ->getEntity()
                ->generateSecurityToken($name, 43200).'" />';
        $html .= '<input type="hidden" name="_rt" value="'.$this->container->getSession()
                ->getEntity()
                ->generateSecurityToken('request_token', 10800).'" class="dp_request_token" />';

        $html .= '<!--DP_FORM_TOKEN_END-->';

        return $html;
    }

    public function securityToken($name = '', $timeout = 43200)
    {
        $session = $this->container->getSession();
        if ($session instanceof Session) {
            return $session->getEntity()->generateSecurityToken($name, $timeout);
        }

        return;
    }

    public function staticSecurityToken($name = '', $timeout = 18000)
    {
        return $this->container->generateStaticSecurityToken($name, $timeout);
    }

    public function staticSecurityTokenSecret($secret, $timeout = 43200)
    {
        return Util::generateStaticSecurityToken($secret, $timeout);
    }

    public function debugVar($var)
    {
        ob_start();
        var_dump($var, true);
        $str = ob_end_clean();

        return $str;
    }

    public function getType($var, $basename = true)
    {
        // Primitive types
        if (!is_object($var)) {
            $var_type = gettype($var);
            // Classes
        } else {
            $var_type = get_class($var);

            if ($basename) {
                $var_type = Util::getBaseClassname($var_type);
            }

            if ($var instanceof \Doctrine\ORM\Proxy\Proxy) {
                $var_type = preg_replace('#(^|\\\\)ApplicationDeskPROEntity(.*?)Proxy$#', '$2', $var_type);
            }
        }

        return $var_type;
    }

    public function getObjectPath($object, array $params = [], $context = 'user')
    {
        /** @var Router $router */
        $router    = RouterUtils::unwrapDecoratedRouter($this->container->get('router'));
        $generator = $router->getGenerator();

        return $generator->generateObjectUrl($object, $params, $context);
    }

    public function getObjectPathAgent($object, array $params = [])
    {
        return $this->getObjectPath($object, $params, 'agent');
    }

    public function compareType($var, $type)
    {
        // Primitive types
        if (!is_object($var)) {
            $var_type = gettype($var);

            return strpos($var_type, $type) !== false;

            // Classes
        } else {
            $var_type = get_class($var);

            // Passes Some\MyClass as well as just MyClass, but not SomeOther\MyClass against Some\MyClass
            return strpos($var_type, $type) !== false and Util::getBaseClassname($var_type) == Util::getBaseClassname($type);
        }
    }

    public function flashMessage($name)
    {
        $session = $this->container->get('session');

        return $session->getFlash($name, null);
    }

    public function encNum($num)
    {
        return Util::baseEncode((int) $num, Util::LETTERS_ALPHABET);
    }

    public function decNum($num)
    {
        return Util::baseDecode((int) $num, Util::LETTERS_ALPHABET);
    }

    public function rand($min = 1, $max = 10)
    {
        return mt_rand((int) $min, (int) $max);
    }

    public function strRepeat($str, $count = 1)
    {
        return str_repeat($str, $count);
    }

    public function strTrim($str, $chars = null)
    {
        return trim($str, $chars);
    }

    public function strLtrim($str, $chars = null)
    {
        return ltrim($str, $chars);
    }

    public function strRtrim($str, $chars = null)
    {
        return rtrim($str, $chars);
    }

    /**
     * A unique ID generator usually used to generate unique element ID's. Unique
     * ID's are generally needed only in the agent interface where things share the same dom.
     *
     * @param string $prefix
     *
     * @return string
     */
    public function elUid($prefix = 'dp_')
    {
        return $prefix
        .Util::baseEncode(time() - strtotime('-15 days'), 'base36') // 4 digits. 15 days to save a few digits
.Util::baseEncode(mt_rand(36, 1295), 'base36') // 2 digits
.Util::baseEncode(Util::requestUniqueId(), 'base36'); // 1-2 digits
    }

    /**
     * Just gets a full helpdesk URL minus the http:// and www bits.
     * Makes it prettier when displaying links in emails.
     *
     * @param       $name
     * @param array $parameters
     *
     * @return mixed|string
     */
    public function urlDisplay($name, array $parameters = [])
    {
        $url = $this->urlFull($name, $parameters);
        $url = preg_replace('#^https?://(www\.)?#i', '', $url);

        return $url;
    }

    public function urlFull($name, array $parameters = [])
    {
        return $this->container->get('router')->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function helpdeskUrl($path)
    {
        return $this->container->getBrandSetting('core.deskpro_url').ltrim($path, '/');
    }

    /**
     * @param string $setting
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getBrandSetting($setting, $default = null)
    {
        return $this->getBrandStack()->getActive()->getSetting($setting, $default);
    }

    /**
     * @param string $setting
     *
     * @return bool
     */
    public function getCrossBrandSetting($setting)
    {
        static $brands   = [];
        static $settings = [];

        if (isset($settings[$setting])) {
            return $settings[$setting];
        }
        if (empty($brands)) {
            /** @var Brand[] $brands */
            $brands = $this->container->getEm()->getRepository(Brand::class)->findAll();
        }

        $brandStack = $this->getBrandStack();

        /** @var BrandAwareSettingsResolver $brandSettingsResolver */
        $brandSettingsResolver = $this->container->get('brand_aware_settings_resolver');

        $value = false;

        foreach ($brands as $brand) {
            $brandStack->push($brand);
            $value = $value || $brandSettingsResolver->getSetting($setting);
            $brandStack->pop();
        }

        $settings[$setting] = $value;

        return $value;
    }

    public function isHelpdeskPath($path)
    {
        if (!preg_match('#^/[^/]#', $path)) {
            $pathinfo = @parse_url($path);
            if (!$pathinfo || !empty($pathinfo['host'])) {
                return false;
            }

            $path = $pathinfo['path'];
        }

        $path      = Strings::canonicalPath($path);
        $root_path = '/'.trim($this->container->get('router')->generate('user', [], RouterInterface::ABSOLUTE_PATH), '/');

        if (!trim($path, '/') || strpos($path, $root_path) !== 0) {
            return false;
        }

        return true;
    }

    public function urlFragment($name, array $parameters = [])
    {
        /** @var Router $router */
        $router = RouterUtils::unwrapDecoratedRouter($this->container->get('router'));

        return $router->getGenerator()->generateFragment($name, $parameters, false);
    }

    public function renderCustomField($display_array, array $vars = [])
    {
        if ($display_array instanceof FormView) {
            return isset($display_array->vars['rendered_data']) ? $display_array->vars['rendered_data'] : null;
        }

        $handler = $display_array['handler'];

        if (is_object($display_array)) {
            $display_array = $display_array->toArray();
        }

        $vars = array_merge($display_array, $vars);

        return $handler->renderHtml($display_array['value'], $vars);
    }

    public function renderCustomFieldForm($display_array, array $vars = [])
    {
        if ($display_array instanceof FormView) {
            $formExtension = $this->container->get('twig')->getExtension('form');

            return $formExtension->renderer->searchAndRenderBlock($display_array, 'widget');
        }

        $handler  = $display_array['handler'];
        $formView = $display_array['formView'];

        if (is_object($display_array)) {
            $display_array = $display_array->toArray();
        }
        $vars = array_merge($display_array, $vars);

        return $handler->renderFormHtml($formView, $vars);
    }

    public function renderCustomFieldText($display_array, array $vars = [])
    {
        if ($display_array instanceof FormView) {
            return $display_array->vars['rendered_data'];
        }

        $handler = $display_array['handler'];

        if (is_object($display_array)) {
            $display_array = $display_array->toArray();
        }
        $vars = array_merge($display_array, $vars);

        return $handler->renderText($display_array['value'], $vars);
    }

    public function getLanguageHtmlAttributes($language = null)
    {
        if (!($language instanceof \Application\DeskPRO\Entity\Language)) {
            $language = $this->container->getTranslator()->getLanguage();
        }

        $attributes = [
            'dir'  => ($language->is_rtl ? 'dir="rtl"' : 'dir="ltr"'),
            'lang' => 'lang="'.htmlspecialchars(substr($language->locale, 0, 2), \ENT_QUOTES, 'UTF-8').'"',
        ];

        return implode(' ', $attributes);
    }

    public function getLanguageArrow($ltr, $rtl = null, $language = null)
    {
        if ($rtl === null) {
            switch ($ltr) {
                case 'right':
                    $ltr = '&rarr;';
                    $rtl = '&larr;';
                    break;
                case 'left':
                    $ltr = '&larr;';
                    $rtl = '&rarr;';
                    break;
                default:
                    return 'unknown';
            }
        }

        if (!($language instanceof \Application\DeskPRO\Entity\Language)) {
            $language = $this->container->getTranslator()->getLanguage();
        }

        if ($language->is_rtl) {
            return $rtl;
        } else {
            return $ltr;
        }
    }

    public function isRtl($language = null)
    {
        if (!($language instanceof \Application\DeskPRO\Entity\Language)) {
            $language = $this->container->getTranslator()->getLanguage();
        }

        return $language->is_rtl;
    }

    public function getPhraseDev($phrase_name, array $vars = [])
    {
        return $this->container->get('deskpro.core.translate')->replaceVarsInString($phrase_name, $vars);
    }

    public function hasPhrase($phrase_name)
    {
        return $this->container->get('deskpro.core.translate')->hasPhrase($phrase_name);
    }

    public function getPhraseText($phrase_name)
    {
        $p = $this->container->get('deskpro.core.translate')->getPhraseText($phrase_name);

        return $p;
    }

    public function getPhraseObject($phrase_name, $property = null)
    {
        return $this->container->get('deskpro.core.translate')->getPhraseObject($phrase_name, $property);
    }

    public function isDebugMode()
    {
        return $this->container->isDebug();
    }

    public function getMd5($string)
    {
        return md5($string);
    }

    public function assetFull($location, $packageName = 'legacy_web')
    {
        $assetHelper = $this->container->get('templating.helper.assets');
        $assetUrl    = $assetHelper->getUrl($location, $packageName);

        if (!preg_match('#^https?://#', $assetUrl)) {
            $url      = $this->container->getBrandSetting('core.deskpro_url');
            $url      = trim(str_replace('/index.php', '', $url), '/');
            $assetUrl = $url.$assetUrl;
        }

        return $assetUrl;
    }

    public function rawUrlEncode($str)
    {
        return rawurlencode($str);
    }

    public function strUpper($str)
    {
        return Strings::utf8_strtoupper($str);
    }

    public function strLower($str)
    {
        return Strings::utf8_strtolower($str);
    }

    public function hex2rgb($hex)
    {
        $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
        $rgb = [];
        if (strlen($hex) == 6) {
            $color_val    = hexdec($hex);
            $rgb['red']   = 0xFF & ($color_val >> 0x10);
            $rgb['green'] = 0xFF & ($color_val >> 0x8);
            $rgb['blue']  = 0xFF & $color_val;
        } elseif (strlen($hex) == 3) {
            $rgb['red']   = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $rgb['green'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $rgb['blue']  = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            return false;
        }

        return $rgb;
    }

    public function captchaHtml($type = 'default')
    {
        $captcha = $this->container->getSystemObject('form_captcha', ['type' => $type]);

        return $captcha->getHtml();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'deskpro_templating';
    }

    public function includeFile($path)
    {
        if (!$this->container->get('deskpro.app_env')->getConfig('sys.tpl.enable_include_file')) {
            return '';
        }

        if (!file_exists($path)) {
            $e = new \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException('File does not exist: '.$path);
            \DpSys\LowError\SystemErrorHandler::logErrorInfo($e);

            return '';
        }

        return SafeFile::fileGetContents($path.dirname($path));
    }

    public function includePhpFile($path, array $with = null)
    {
        if (!$this->container->get('deskpro.app_env')->getConfig('sys.tpl.enable_include_file')) {
            return '';
        }

        if ($with !== null) {
            extract($with, \EXTR_SKIP);
        }

        if (!file_exists($path)) {
            $e = new \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException('File does not exist: '.$path);
            \DpSys\LowError\SystemErrorHandler::logException($e, false, 'tpl_include_php_file');

            return '';
        }

        ob_start();
        include $path;
        $content = ob_get_clean();

        return $content;
    }

    public function includeCodePlugin($locationName)
    {
        return DpPlugins::getManager()->getCustomHtml($locationName, $this->container);
    }

    public function dumpVar($var)
    {
        return \DpSys\LowError\SystemErrorHandler::varToString($var);
    }

    public function urlTrimScheme($url, $trim_adv = false)
    {
        $ret = preg_replace('#^https?://#i', '', $url);

        if ($trim_adv) {
            $ret = preg_replace('#^www\.#i', '', $ret);

            // Hashes/tokens
            $ret = preg_replace('#([a-zA-Z0-9\-_]+)=([a-zA-Z0-9]{32})&?#', '', $ret);
            $ret = preg_replace('#([a-zA-Z0-9\-_]+)=([a-zA-Z0-9]{40})&?#', '', $ret);
            $ret = preg_replace('#([a-zA-Z0-9\-_]+)=([a-zA-Z0-9]{6})\-([a-zA-Z]{10})\-([a-zA-Z0-9]{40})&?#', '', $ret);

            $ret = trim($ret, '/?#&');

            // Trailing index.php, index.html
            $ret = preg_replace('#/index\.(html|php)#', '', $ret);
        }

        return $ret;
    }

    public function countryName($code)
    {
        $name = Countries::getCountryFromCode($code);
        if (!$name) {
            return $code;
        }

        return $name;
    }

    protected $_widgetCache = [];

    public function getWidgets($baseId, $page, $location, $position = '*', $data = [])
    {
        return '';
        $widgets = $this->_getPageLocationWidgets($page, $location, $position);
        if (!$widgets) {
            return '';
        }

        $output = '';
        foreach ($widgets as $widget) {
            $output .= $this->_insertWidget($baseId, $widget,
                '<div class="profile-box-container" id="{id}_container">'
                .'<header><h4 id="{id}_tab">{title}</h4></header>'
                .'<section class="widget-content" id="{id}" data-widget="{widget}">{html}</section>'
                .'</div>',
                $data
            );
        }

        return $output;
    }

    public function getWidgetsRaw($page, $location, $position = '')
    {
        return '';

        return $this->_getPageLocationWidgets($page, $location, $position);
    }

    protected function _getPageLocationWidgets($page, $location, $position = '')
    {
        return [];
        if (!array_key_exists($page, $this->_widgetCache)) {
            $this->_widgetCache[$page] = App::getEntityRepository('DeskPRO:Widget')->getEnabledPageWidgetsGrouped($page);
        }

        if (empty($this->_widgetCache[$page][$location])) {
            return [];
        } else {
            if ($position === '') {
                $output = [];
                foreach ($this->_widgetCache[$page][$location] as $widgets) {
                    foreach ($widgets as $widget) {
                        $output[] = $widget;
                    }
                }

                return $output;
            } elseif (!empty($this->_widgetCache[$page][$location][$position])) {
                return $this->_widgetCache[$page][$location][$position];
            } else {
                return [];
            }
        }
    }

    public function getWidgetHtmlId($baseId, $widget)
    {
        return '';
    }

    protected function _insertWidget($baseId, $widget, $wrapper, $data = [])
    {
        return '';
    }

    protected function _replaceWidgetPlaceholders($content, $data, $context)
    {
        return $content;
    }

    public function getWidgetTabsHeader($baseId, $page, $location, array $tabs)
    {
        $originalCount = count($tabs);

        foreach ($this->_getPageLocationWidgets($page, $location, 'tab') as $widget) {
            $htmlId        = $this->getWidgetHtmlId($baseId, $widget);
            $tabs[$htmlId] = $widget->title;
        }

        foreach ($tabs as $key => $title) {
            if ($title === false) {
                unset($tabs[$key]);
            }
        }

        if (!$tabs) {
            return '';
        } elseif (count($tabs) == 1 && $originalCount == 1) {
            return '<h4>'.reset($tabs).'</h4>';
        } else {
            $tabHtml = [];
            $on      = false;
            foreach ($tabs as $id => $title) {
                if (!$on) {
                    $onHtml = ' class="on"';
                    $on     = true;
                } else {
                    $onHtml = '';
                }
                $tabHtml[] = '<li data-tab-for="#'.$id.'" id="'.$id.'_tab"'.$onHtml.'>'.$title.'</li>';
            }

            return '<nav data-element-handler="DeskPRO.ElementHandler.SimpleTabs"><ul>'.implode('', $tabHtml).'</ul></nav>';
        }
    }

    public function getWidgetTabsBody($baseId, $page, $location, $wrapper, $data = [])
    {
        return '';
    }

    public function getJsSsoLoader($interface = 'user')
    {
        ///////////////////////////////////////////////////////////////////////
        // Settings
        $person        = App::getCurrentPerson();
        $is_first_page = $this->container->getSession()->isFirstPage(); // not to be trusted
        /** @var \Application\DeskPRO\Auth\AuthSettings $auth_settings */
        $auth_settings           = $this->container->getSystemService('auth_settings');
        $auth_interface_settings = $interface == 'user' ? $auth_settings->getUserInterfaceSettings() : $auth_settings->getAgentInterfaceSettings();
        /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
        $request_stack = $this->getContainer()->get('request_stack');

        ///////////////////////////////////////////////////////////////////////
        // Ensure GET request
        if (!$request = $request_stack->getCurrentRequest()) {
            return '';
        }
        if ('GET' !== $request->getMethod()) {
            return '';
        }

        ///////////////////////////////////////////////////////////////////////
        // If the user just logged out, we don't want to be logging him in immediatly
        if ($request->query->get('o')) {
            return '';
        }

        ///////////////////////////////////////////////////////////////////////
        // Get iFrame Output, if any
        $iFrameOutput = '';
        if ($auth_interface_settings->isBackgroundSsoEnabled()) {
            $adapter = $auth_interface_settings->getSsoAuthAdapter(SsoLoginActionInterface::CONTEXT_BACKGROUND);

            if ($adapter instanceof IframeSsoInterface) {
                $vars = array_merge(
                    [
                        'iframe_url' => '',
                        'render'     => true,
                    ],
                    $adapter->getIframeTemplateParams($is_first_page)
                );

                $iFrameOutput = $this->getTemplating()->render(
                    'DeskPRO:Auth:_sso_iframe.html.twig',
                    $vars
                );
            }
        }

        ///////////////////////////////////////////////////////////////////////
        // Some old apps use this code for background authentication
        // needs to stay because Magento native app still uses this
        $legacyOutput = $this->legacyMagentoPluginCode($interface, $person, $is_first_page);

        return $iFrameOutput.$legacyOutput;
    }

    /**
     * @param $interface
     * @param $person
     * @param $is_first_page
     *
     * @return array
     */
    protected function legacyMagentoPluginCode($interface, $person, $is_first_page)
    {
        /** @var \Application\DeskPRO\Usersource\UsersourceManager $us_manager */
        $us_manager = $this->container->getSystemService('usersource_manager');
        $sources    = $us_manager->getAll()->forInterface($interface)->withCapability(
            UsersourceInfo::CAPABILITY_SSO_JS
        );
        $output = [];
        foreach ($sources as $source) {
            /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $factory */
            $factory = $this->container->getSystemService('usersource_auth_adapter_factory');
            $adapter = $factory->getAuthAdapter($source, SsoLoginActionInterface::CONTEXT_BACKGROUND);

            if ($adapter instanceof JsSsoInterface) {
                $output[] = $adapter->getSsoHtmlLoaderOutput($source, $this, $person, $is_first_page);
            }
        }

        return implode("\n\n", $output);
    }

    public function getJsSsoShare()
    {
        $person = App::getCurrentPerson();

        if (!$person || $person->isGuest()) {
            return '';
        }

        $output = [];
        foreach ($person->usersource_assoc as $assoc) {
            $us = $assoc->usersource;
            if (!$us->isCapable(UsersourceInfo::CAPABILITY_SHARE_SESSION)) {
                continue;
            }

            $adapter  = $us->getAdapter()->getAuthAdapter();
            $output[] = $adapter->getSsoShareSessionHtml($assoc->identity);
        }

        return implode("\n\n", $output);
    }

    /**
     * This gets an attribute from an array without casting the key to
     * an int or a string. This is useful when the key is a number that
     * is larger than what an int can hold.
     *
     * @param array $array
     * @param mixed $key
     *
     * @return mixed
     */
    public function getArrayAttribute($array, $key)
    {
        return array_key_exists($key, $array) ? $array[$key] : null;
    }

    public function countLines($str)
    {
        if (is_object($str) && method_exists($str, '__toString')) {
            $str = (string) $str;
        }
        if (!is_scalar($str)) {
            return 0;
        }

        $str = Strings::standardEol($str);

        return substr_count($str, "\n") + 1;
    }

    public function min()
    {
        $args = func_get_args();

        return call_user_func_array('min', $args);
    }

    public function max()
    {
        $args = func_get_args();

        return call_user_func_array('max', $args);
    }

    public function plain_template_filter($content)
    {
        $content = RegexUtils::safePregReplace('#<\s*script#i', '<deskpro_script', $content);
        $content = RegexUtils::safePregReplace('#<\s*/\s*script#i', '</deskpro_script', $content);

        return $content;
    }

    public function match($str, $regex)
    {
        $regex = Strings::getInputRegexPattern($regex);
        if (!$regex) {
            return false;
        }

        return RegexUtils::safePregMatch($regex, $str);
    }

    public function set_tplvar($context, $k, $v)
    {
        if (!isset($context['tplvars'])) {
            $context['tplvars'] = new \stdClass();
        }

        $context['tplvars']->$k = $v;

        return;
    }

    public function getTplSourceTemplate($id, $name)
    {
        $source = $this->container->getTemplating()->getSource($name);
        $source = str_replace('<script>', '%startScript%', $source);
        $source = str_replace('</script>', '%endScript%', $source);
        $source = '<script type="text/x-deskpro-tmpl" id="'.$id.'">'.$source.'</script>';

        return $source;
    }

    public function ngVar($var)
    {
        return '{{'.$var.'}}';
    }

    public function ngPluralPhrase($phrase_name)
    {
        $positions = [];

        for ($i = 0; $i < 5; ++$i) {
            $text = $this->container->getTranslator()->getPhraseTextCount($phrase_name, $i);
            $text = str_replace('{{count}}', '{}', $text);
            if (!in_array($text, $positions, true)) {
                $positions[$i] = $text;
            }
        }

        if (count($positions) == 2) {
            $positions['other'] = $positions[0];
            unset($positions[0]);
        } else {
            if (!isset($positions[0])) {
                Arrays::unshiftAssoc(
                    $positions,
                    '0',
                    Arrays::getFirstItem($positions)
                );
            }
            $positions['other'] = Arrays::getLastItem($positions);
        }

        // Must always specify 1 because ng on admin side uses en_US
        if (!isset($positions[1])) {
            $positions[1] = $positions['other'];
        }

        return json_encode($positions);
    }

    public function ngBind($var)
    {
        return '<span ng-bind="'.htmlspecialchars($var).'"></span>';
    }

    public function ngStaticVar($var)
    {
        return '<span bo-bind="'.htmlspecialchars($var).'"></span>';
    }

    public function ngIncTpl($context, $tpl_name, $save_name = null)
    {
        $name = $tpl_name;

        $tpl = $this->container->getTemplating();
        if (!$tpl->exists($name)) {
            return '<!-- No such template exists: '.$name.' -->';
        }

        $rendered = $tpl->render($name, $context);

        if (!$save_name) {
            $save_name = $tpl_name;
            $save_name = preg_replace('#^(AdminInterface|Admin|Agent)Bundle:#', '$1/', $save_name);
            $save_name = str_replace(':', '/', $save_name);
            $save_name = preg_replace('#\.twig$#', '', $save_name);
        }

        if (strpos($rendered, '<script') !== false) {
            // If it has inner script tags we should be using our special dp-ng-template tag and encode the tpl as json
            $json = \Application\DeskPRO\Util::jsonEncode(['template' => $rendered]);
            $html = '<script type="text/dp-ng-template" id="'.$save_name.'">'.$json.'</script>';
        } else {
            $html = '<script type="text/ng-template" id="'.$save_name.'">'.$rendered.'</script>';
        }

        return $html;
    }

    public function ngHref($route, $params = '{}')
    {
        if (is_array($params)) {
            $params = json_encode($params);
        }

        return '{{ state_path(\''.addslashes($route).'\','.$params.') }}';
    }

    public function ngHrefVar($route_var, $params = '{}')
    {
        return '{{ state_path('.$route_var.', '.str_replace(["'", '"'], ['&apos;', '&quot;'], $params).') }}';
    }

    public function smartWrap($string, $len = 50, $break = null)
    {
        if ($break === null) {
            $break = Strings::ZERO_WIDTH_SPACE;
        }

        return Strings::smartWordWrap($string, $len, $break);
    }

    public function getDpAppLocation($base_id, $loc_name)
    {
        $loc_id = preg_replace('#[^a-zA-Z0-9_]#', '_', $loc_name);

        return '<div id="'.$base_id.'_'.$loc_id.'" class="dp-app-context-container as-default-hidden" data-location-name="'.$loc_name.'"></div>';
    }

    public function jsonEncodeInHtml($data)
    {
        return \Application\DeskPRO\Util::jsonEncode($data);
    }

    public function textWrapMarks($string, $length)
    {
        // Inserts a 0-width space at position $length
        // Browsers will wrap at this point in long strings
        return RegexUtils::safePregReplace('/(.{'.$length.'})/u', '$1'.Strings::chrUtf8(8203), $string);
    }

    public function regexReplace($string, $regex, $replace, $limit = -1)
    {
        $regex = Strings::getInputRegexPattern($regex);

        if (!$regex) {
            return $string;
        }

        $result = RegexUtils::safePregReplace($regex, $replace, $string, $limit);

        if ($result === null) {
            return $string;
        }

        return $result;
    }

    public function serverCapable($what)
    {
        switch ($what) {
            case 'imap':
                return extension_loaded('imap');
            case 'soap':
                return extension_loaded('soap');
            case 'curl':
                return extension_loaded('curl');
        }

        return false;
    }

    public function js_error_tracking($loc, array $options = [])
    {
        return '';
    }

    public function renderTicketTemplate($string, Ticket $ticket, ExecutorContextInterface $context, array $extra_vars = [])
    {
        // Simple string, cant be a template so dont waste time evaluating it
        if (strpos($string, '{{') === false && strpos($string, '{%') === false) {
            return $string;
        }

        $vars = [
            'performer'     => $context->getPersonContext(),
            'helpdesk_name' => $this->getContainer()->getBrandSetting('core.deskpro_name'),
            'site_name'     => $this->getContainer()->getBrandSetting('core.site_name'),
            'user_vars'     => $context->getUserVars(),
        ];

        if ($extra_vars) {
            $vars = array_merge($vars, $extra_vars);
        }

        if (!isset($vars['ticket'])) {
            $vars['ticket'] = $ticket->toApiData();
        }

        try {
            $tr       = new TwigTemplateRenderer($this->getContainer()->getTwig(), $this->getContainer()->get('brand_aware_settings_resolver'));
            $rendered = $tr->renderStringTemplate($string, $vars);
        } catch (\Exception $e) {
            return $string;
        }

        return $rendered;
    }

    /**
     * @return bool
     */
    public function hasLoginForm()
    {
        /** @var UsersourceManager $usersourceManager */
        $usersourceManager = $this->container->getSystemService('usersource_manager');

        $count = $usersourceManager
            ->getAll()
            ->mustBeEnabled()
            ->withCapability(UsersourceInfo::CAPABILITY_FORM_LOGIN)
            ->count();

        return $count > 0;
    }

    /**
     * @return string
     */
    public function staticGetUserCopyrightHtml()
    {
        return License::staticGetUserCopyrightHtml();
    }

    /**
     * @param mixed $ret
     *
     * @return mixed
     */
    public function dummy($ret)
    {
        return $ret;
    }

    public function replaceContentPdf($content)
    {
        return $this->replaceContent($content, true);
    }

    public function replaceContent($content, $pdf = false)
    {
        return preg_replace_callback_array(
            [
                '|{{\s*img\(([^/]+)/([^)]+)\)\s*}}|' => function ($match) {
                    return $this->getBlobImage(trim($match[1]), trim($match[2]));
                },
                '|<a href="{{\s*content\(([^,]+),([^),]+)\)\s*}}">([^<]*)</a>|' => function ($match) use ($pdf) {
                    $type = trim($match[1]);
                    $id = trim($match[2]);
                    $title = empty($match[3]) ? '' : trim($match[3]);

                    return $this->getManualInternalLink($type, $id, $title, '', $pdf);
                },
                '|{{\s*content_link\(([^,]+),([^),]+)(,[^)]+)?\)\s*}}|' => function ($match) use ($pdf) {
                    $type = trim($match[1]);
                    $id = trim($match[2]);
                    $anchor = empty($match[3]) ? '' : trim($match[3], ", \t\n\r\0\x0B");

                    return $this->getManualInternalLink($type, $id, '', $anchor, $pdf);
                },
            ],
            $content
        );
    }

    public function getBlobImage($authId, $filename)
    {
        return App::get('router')->generate('serve_blob', ['blob_auth_id' => $authId, 'filename' => $filename], UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function getManualInternalLink($type, $id, $title = '', $anchor = '', $pdf = false)
    {
        $em = $this->getContainer()->getEm();
        switch ($type) {
            case 'article':
            case 'knowledgebase':
            case 'knowledgebase_article':
                $object = $em->getRepository(Article::class)->find($id);
                break;
            case 'news':
                $object = $em->getRepository(News::class)->find($id);
                break;
            case 'feedback':
                $object = $em->getRepository(Feedback::class)->find($id);
                break;
            case 'download':
                $object = $em->getRepository(Download::class)->find($id);
                break;
            case 'guide':
            case 'topic':
                $object = $em->getRepository(Topic::class)->find($id);
                break;
            default:
                $object = null;
        }
        if (!$object) {
            return '-- Broken link - '.$type.':'.$id.' --';
        }
        $url = $this->getContainer()->get('object_router')->getPortalUrl($object);
        if ($anchor) {
            $url .= '#'.$anchor;
        }
        if (!$title) {
            $title = $object->getTitle();
        }

        if ($pdf && $type == 'topic') {
            $target = $object->getSlug();
            if ($anchor) {
                $target .= '_'.$anchor;
            }

            return '<a class="internal_link topic" href="#'.$target.'">'.$title.'</a>';
        } else {
            return '<a class="internal_link '.$type.'" href="'.$url.'">'.$title.'</a>';
        }
    }

    /**
     * @param array $groupedInfo
     * @param mixed $category
     *
     * @return int
     */
    public function calcGroupedHierarchyCount($groupedInfo, array $category)
    {
        $count = isset($groupedInfo['counts'][$category['id']]['total']) ? $groupedInfo['counts'][$category['id']]['total'] : 0;

        if (isset($category['children'])) {
            foreach ($category['children'] as $childCategory) {
                $count += $this->calcGroupedHierarchyCount($groupedInfo, $childCategory);
            }
        }

        return $count;
    }

    /**
     * @param int $currencyId
     *
     * @return string
     */
    public function getCurrency($currencyId)
    {
        if ($currencyId) {
            return $this->getContainer()->getEm()->getRepository(Currency::class)->find($currencyId);
        }

        return;
    }
}
