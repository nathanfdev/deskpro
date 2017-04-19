<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\SendmailBundle\Twig\Extension;

use Application\DeskPRO\App;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Templating\GlobalVariables;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Usersource\UsersourceInfo;
use DeskPRO\Bundle\AppBundle\Routing\RouterUtils;
use DeskPRO\Bundle\SendmailBundle\Twig\TokenParser\CalloutParser;
use DeskPRO\Bundle\SendmailBundle\Twig\TokenParser\ColumnParser;
use DeskPRO\Bundle\SendmailBundle\Twig\TokenParser\ContainerParser;
use DeskPRO\Bundle\SendmailBundle\Twig\TokenParser\RowParser;
use DeskPRO\Bundle\SendmailBundle\Twig\TokenParser\SpacerParser;
use DeskPRO\Bundle\SendmailBundle\Twig\TokenParser\WrapperParser;
use DeskPRO\Component\Util\RegexUtils;
use DpSys\License;
use Orb\Auth\Adapter\IframeSsoInterface;
use Orb\Auth\Adapter\JsSsoInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;
use Orb\Data\Countries;
use Orb\Util\Arrays;
use Orb\Util\Dates;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class TemplatingExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;
    /** @var array */
    protected $counterRegistry;

    /**
     * TemplatingExtension constructor.
     *
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'app'         => new GlobalVariables($this->container),
            'default_css' => 'test',
        ];
    }

    /**
     * @return DeskproContainer|\Symfony\Component\DependencyInjection\ContainerInterface
     */
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
            new \Twig_SimpleFunction('phrase', [$this, 'getPhrase'], ['is_safe' => ['html'], 'needs_context' => true]),
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
            new \Twig_SimpleFunction('var_dump', [$this, 'dumpVar']),
            new \Twig_SimpleFunction('dp_copyright', [$this, 'staticGetUserCopyrightHtml'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('dp_app_loc', [$this, 'getDpAppLocation'], ['is_safe' => ['html']]),
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

            // override so we can suppress errors where templates are out of date
            new \Twig_SimpleFunction('url', [$this, 'getUrl']),
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
            new \Twig_SimpleFilter('date', [$this, 'userDate'], ['needs_context' => true]),
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

            // Override for custom UTF-8 handling
            new \Twig_SimpleFilter('upper', [$this, 'strUpper']),
            new \Twig_SimpleFilter('lower', [$this, 'strLower']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new CalloutParser(),
            new ColumnParser(),
            new ContainerParser(),
            new RowParser(),
            new SpacerParser(),
            new WrapperParser(),
        ];
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @throws \Exception
     *
     * @return string
     */
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

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @throws \Exception
     *
     * @return string
     */
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

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getBaseTemplateName($name)
    {
        $parts = explode(':', $name);
        $name  = array_pop($parts);
        $name  = str_replace('.html.twig', '', $name);

        return $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
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

    /**
     * @param $size
     *
     * @return string
     */
    public function filesizeDisplay($size)
    {
        if ($size < 0) {
            return 'n/a';
        }

        return Numbers::filesizeDisplay($size);
    }

    /**
     * @param int $time
     *
     * @return mixed|string
     */
    public function getTimeGroupPhrase($time)
    {
        static $timePhrases = [
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

        foreach ($timePhrases as $min => $phrase) {
            if ($time <= $min) {
                return $phrase;
            }
        }

        return '> 6 months';
    }

    /**
     * @param string $email
     * @param int    $size
     *
     * @return string
     */
    public function gravatar($email, $size = 80)
    {
        $hash = strtolower(md5($email));
        $url  = 'http://www.gravatar.com/avatar/'.$hash.'?';
        $url .= '&d='.$this->container->getRouter()->generate('serve_default_picture', ['s' => $size], UrlGeneratorInterface::ABSOLUTE_URL);

        return $url;
    }

    /**
     * @param $var
     *
     * @return mixed|void
     */
    public function getFirst($var)
    {
        if (!$var) {
            return;
        }

        return Arrays::getFirstItem($var);
    }

    /**
     * @param $var
     *
     * @return mixed|void
     */
    public function getLast($var)
    {
        if (!$var) {
            return;
        }

        return Arrays::getLastItem($var);
    }

    /**
     * @param $var
     *
     * @return mixed
     */
    public function isArray($var)
    {
        return is_array($var);
    }

    /**
     * @param string $method
     *
     * @return mixed
     */
    public function getInstanceAbility($method)
    {
        $method = Strings::underscoreToCamelCase($method);

        return $this->container->getSystemService('instance_ability')->$method();
    }

    /**
     * @param string $name
     * @param array  $params
     * @param array  $namedParams
     * @param bool   $html
     *
     * @return mixed
     */
    public function getServiceUrl($name, $params = null, $namedParams = null, $html = true)
    {
        if (!$params || !is_array($params)) {
            $params = null;
        }
        if (!$namedParams || !is_array($namedParams)) {
            $params = null;
        }

        return $this->container->get('deskpro.service_urls')->get($name, $params, $namedParams, $html);
    }

    /**
     * @param string $name
     * @param array  $params
     * @param array  $namedParams
     *
     * @return mixed
     */
    public function getServiceUrlRaw($name, $params = null, $namedParams = null)
    {
        return $this->getServiceUrl($name, $params, $namedParams, false);
    }

    /**
     * @param int $secs
     * @param int $detail
     *
     * @throws \Exception
     *
     * @return string
     */
    public function relativeTime($secs, $detail = 2)
    {
        return Dates::secsToReadable($secs, $detail);
    }

    /**
     * @param string $str
     * @param int    $width
     * @param bool   $dots
     *
     * @return mixed
     */
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

    /**
     * @param string $name
     * @param int    $start
     *
     * @return string
     */
    public function startCounter($name = 'default', $start = 1)
    {
        $this->counterRegistry[$name] = $start;

        return '';
    }

    /**
     * @param string $name
     *
     * @return int|mixed
     */
    public function getCounter($name = 'default')
    {
        return isset($this->counterRegistry[$name]) ? $this->counterRegistry[$name] : 0;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function incCounter($name = 'default')
    {
        if (!isset($this->counterRegistry[$name])) {
            $this->counterRegistry[$name] = 0;
        }

        $v = $this->counterRegistry[$name]++;

        return $v;
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function getUrlDomain($string)
    {
        $urlinfo = @parse_url($string);
        if (!$urlinfo) {
            return $string;
        }

        return @$urlinfo['host'];
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function crc32($string)
    {
        $string = (string) $string;

        return sprintf('%u', crc32($string));
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function safeLinkUrlsHtml($text)
    {
        return Strings::linkifyHtml($text, true);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function safeLinkUrls($text)
    {
        $text = htmlspecialchars($text);

        return Strings::linkifyHtml($text, true);
    }

    /**
     * @param string $html
     *
     * @return mixed
     *
     * @todo ensure we stacked the proper brand before calling
     */
    public function linkAgentShortCodeHtml($html)
    {
        $idMap = [
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

        foreach ($idMap as $prefix => $info) {
            $html = RegexUtils::safePregReplace(
                '/\{\{\s*'.$prefix.'-(\d+)\s*\}\}/',
                '<a href="'.$url.$info[1].'$1">'.$info[0].' #$1</a>',
                $html
            );
        }

        return $html;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAssetic($name)
    {
        $asseticManager = $this->container->getSystemService('assetic_manager');

        return $asseticManager->getUrl($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAsseticRaw($name)
    {
        $asseticManager = $this->container->getSystemService('assetic_manager');

        return $asseticManager->getRawUrls($name);
    }

    /**
     * @param        $array
     * @param string $sep
     *
     * @return string
     */
    public function implodeArray($array, $sep = ', ')
    {
        if (!$array || !is_array($array)) {
            return '';
        }

        return implode($array, $sep);
    }

    /**
     * @param string $string
     * @param string $del
     *
     * @return array
     */
    public function explodeString($string, $del = ',')
    {
        $ret    = [];
        $string = (string) $string;

        foreach (explode($del, $string) as $p) {
            $ret[] = trim($p);
        }

        return $ret;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function stripHtml($str)
    {
        return Strings::html2Text($str);
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    public function stripLinebreaks($str)
    {
        $str = str_replace(["\r\n", "\n"], ' ', $str);
        $str = str_replace(['<br />', '<br/>', '<br>'], ' ', $str);
        $str = str_replace(['<p>', '</p>', '<p />', '<p/>'], ' ', $str);

        return $str;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return mixed
     */
    public function htmlGetAssetic($name, $options = [])
    {
        $rawPacks           = $this->container->get('settings_resolver')->getGlobalSettings()->get('raw_assets');
        $lessUseCss         = App::getConfig('debug.less_use_css_dir', false);
        $disableClientCache = App::getConfig('debug.disable_client_cache', false);

        if ($rawPacks && (in_array($name, $rawPacks) or in_array('all', $rawPacks) or (in_array('all -vendors', $rawPacks) && $name != 'agent_vendors'))) {
            $urls = $this->getAsseticRaw($name);
        } else {
            $urls = [$this->getAssetic($name)];
        }

        $qsAppend = ($disableClientCache ? time() : DP_BUILD_TIME);

        if (App::getConfig('asset_version_id')) {
            $qsAppend = App::getConfig('asset_version_id');
        }

        $html = [];

        foreach ($urls as $url) {
            $type = Strings::getExtension($url);

            $url .= '?'.$qsAppend;

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

                    if ($lessUseCss && strpos($url, '/stylesheets-less/') !== false) {
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

    /**
     * @param int $id
     *
     * @return array|null
     */
    public function getData($id)
    {
        switch ($id) {
            case 'country_names':
                return Countries::getCountryNames();
                break;
            case 'countries':
                return Countries::getCountryArray();
                break;
            case 'us_states':
                return Countries::getUsStates();
                break;
            case 'timezones':
                $tzs = \DateTimeZone::listIdentifiers();
                $tzs = array_combine($tzs, $tzs);

                foreach ($tzs as &$tzName) {
                    $tzName = str_replace('/', ' ▸ ', $tzName);
                    $tzName = str_replace('_', ' ', $tzName);
                }

                return $tzs;
                break;
            default:
                return;
        }
    }

    /**
     * @param string $string
     * @param array  $words
     *
     * @return mixed
     */
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

    /**
     * @param string $str
     *
     * @return string
     */
    public function slugify($str)
    {
        return Strings::slugifyTitle($str);
    }

    /**
     * @param        $context
     * @param        $date
     * @param string $format
     * @param null   $timezone
     *
     * @return string
     */
    public function userDate($context, $date, $format = 'fulltime', $timezone = null)
    {
        // Backwards compat calls: args shifted back one
        if (!is_array($context)) {
            $args = func_get_args();
            if (!isset($args[1])) {
                $args[1] = 'F j, Y H:i';
            }
            if (!isset($args[2])) {
                $args[2] = null;
            }

            list($date, $format, $timezone) = $args;
            $context                        = null;
        }

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

        if (!($date instanceof \DateTime)) {
            if (ctype_digit((string) $date)) {
                $date = new \DateTime('@'.$date);
                $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            } else {
                try {
                    $dateStr = $date;
                    $date    = new \DateTime($dateStr);
                } catch (\Exception $e) {
                }
            }
        }

        if (!($date instanceof \DateTime)) {
            $dateStr = (string) $date;

            return "invalid_date($dateStr)";
        }

        if ($timezone === null && $context && isset($context['context']['person_timezone'])) {
            $timezone = $context['context']['person_timezone'];
        }

        if ($timezone === null && App::getCurrentPerson()) {
            $timezone = App::getCurrentPerson();
        }

        if ($timezone instanceof Person) {
            $timezone = $timezone->getDateTimezone();
        }

        if (null !== $timezone) {
            if (!($timezone instanceof \DateTimeZone)) {
                $timezone = new \DateTimeZone($timezone);
            }
        }

        if (!$timezone || $timezone == 'UTC') {
            $timezone = new \DateTimeZone('UTC');
        }

        $date->setTimezone($timezone);

        $prefix = 'user.time.';
        if (DP_INTERFACE == 'admin' || DP_INTERFACE == 'agent') {
            $prefix = 'agent.time.';
        }

        return $this->container->getTranslator()->date($format, $date, $prefix);
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    public function jqueryUiDateFormat($format)
    {
        // Map of PHP symbols to jQuery date format symbols
        static $phpSym = [
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

        $formatLen = strlen($format);
        $newFormat = [];
        $escaping  = false;

        for ($i = 0; $i < $formatLen; ++$i) {
            $char = $format[$i];
            if ($char === '\\') {
                ++$i;
                if ($escaping) {
                    $newFormat[] = $format[$i];
                } else {
                    $newFormat[] = '\''.$format[$i];
                }
                $escaping = true;
            } else {
                if ($escaping) {
                    $newFormat[] = "'";
                    $escaping    = false;
                }
                if (isset($phpSym[$char])) {
                    $newFormat[] = $phpSym[$char];
                } else {
                    $newFormat[] = $char;
                }
            }
        }

        return implode('', $newFormat);
    }

    /**
     * @param int $length
     * @param int $maxUnit
     *
     * @return string
     */
    public function timeLength($length, $maxUnit = null)
    {
        return \Application\DeskPRO\Util::getPrintableTimeLength($length, $maxUnit);
    }

    /**
     * @param string $format
     *
     * @return string
     */
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

    /**
     * @param string $name
     * @param string $fieldName
     *
     * @return string
     */
    public function formToken($name = '', $fieldName = '_dp_security_token')
    {
        if (!$this->container->getSession()->getEntity()->getPersonId()) {
            $html = "<!--DP_FORM_TOKEN($name, $fieldName)-->";
        } else {
            $html = '';
        }

        $html .= '<input type="hidden" name="'.$fieldName.'" value="'.$this->container->getSession()
                ->getEntity()
                ->generateSecurityToken($name, 43200).'" />';
        $html .= '<input type="hidden" name="_rt" value="'.$this->container->getSession()
                ->getEntity()
                ->generateSecurityToken('request_token', 10800).'" class="dp_request_token" />';

        $html .= '<!--DP_FORM_TOKEN_END-->';

        return $html;
    }

    /**
     * @param string $name
     * @param int    $timeout
     *
     * @return string
     */
    public function securityToken($name = '', $timeout = 43200)
    {
        return $this->container->getSession()->getEntity()->generateSecurityToken($name, $timeout);
    }

    /**
     * @param string $name
     * @param int    $timeout
     *
     * @return string
     */
    public function staticSecurityToken($name = '', $timeout = 18000)
    {
        return $this->container->generateStaticSecurityToken($name, $timeout);
    }

    /**
     * @param string $secret
     * @param int    $timeout
     *
     * @return string
     */
    public function staticSecurityTokenSecret($secret, $timeout = 43200)
    {
        return Util::generateStaticSecurityToken($secret, $timeout);
    }

    /**
     * @param $var
     *
     * @return mixed
     */
    public function debugVar($var)
    {
        ob_start();
        var_dump($var, true);
        $str = ob_end_clean();

        return $str;
    }

    /**
     * @param mixed $var
     * @param bool  $basename
     *
     * @return string
     */
    public function getType($var, $basename = true)
    {
        // Primitive types
        if (!is_object($var)) {
            $varType = gettype($var);
            // Classes
        } else {
            $varType = get_class($var);

            if ($basename) {
                $varType = Util::getBaseClassname($varType);
            }

            if ($var instanceof \Doctrine\ORM\Proxy\Proxy) {
                $varType = preg_replace('#(^|\\\\)ApplicationDeskPROEntity(.*?)Proxy$#', '$2', $varType);
            }
        }

        return $varType;
    }

    /**
     * @param object $object
     * @param array  $params
     * @param string $context
     *
     * @return mixed
     */
    public function getObjectPath($object, array $params = [], $context = 'user')
    {
        /** @var Router $router */
        $router    = RouterUtils::unwrapDecoratedRouter($this->container->get('router'));
        $generator = $router->getGenerator();

        return $generator->generateObjectUrl($object, $params, $context);
    }

    /**
     * @param object $object
     * @param array  $params
     *
     * @return mixed
     */
    public function getObjectPathAgent($object, array $params = [])
    {
        return $this->getObjectPath($object, $params, 'agent');
    }

    /**
     * @param mixed  $var
     * @param string $type
     *
     * @return bool
     */
    public function compareType($var, $type)
    {
        // Primitive types
        if (!is_object($var)) {
            $varType = gettype($var);

            return strpos($varType, $type) !== false;

            // Classes
        } else {
            $varType = get_class($var);

            // Passes Some\MyClass as well as just MyClass, but not SomeOther\MyClass against Some\MyClass
            return strpos($varType, $type) !== false and Util::getBaseClassname($varType) == Util::getBaseClassname($type);
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function flashMessage($name)
    {
        $session = $this->container->get('session');

        return $session->getFlash($name, null);
    }

    /**
     * @param int $num
     *
     * @return string
     */
    public function encNum($num)
    {
        return Util::baseEncode((int) $num, Util::LETTERS_ALPHABET);
    }

    /**
     * @param int $num
     *
     * @return int
     */
    public function decNum($num)
    {
        return Util::baseDecode((int) $num, Util::LETTERS_ALPHABET);
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return mixed
     */
    public function rand($min = 1, $max = 10)
    {
        return mt_rand((int) $min, (int) $max);
    }

    /**
     * @param string $str
     * @param int    $count
     *
     * @return mixed
     */
    public function strRepeat($str, $count = 1)
    {
        return str_repeat($str, $count);
    }

    /**
     * @param string $str
     * @param array  $chars
     *
     * @return mixed
     */
    public function strTrim($str, $chars = null)
    {
        return trim($str, $chars);
    }

    /**
     * @param string $str
     * @param array  $chars
     *
     * @return mixed
     */
    public function strLtrim($str, $chars = null)
    {
        return ltrim($str, $chars);
    }

    /**
     * @param string $str
     * @param array  $chars
     *
     * @return mixed
     */
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
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed|string
     */
    public function urlDisplay($name, array $parameters = [])
    {
        $url = $this->getUrl($name, $parameters);
        $url = preg_replace('#^https?://(www\.)?#i', '', $url);

        return $url;
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @deprecated - use getUrl instead
     *
     * @return mixed
     */
    public function urlFull($name, array $parameters = [])
    {
        return $this->getUrl($name, $parameters);
    }

    /**
     * @param $path
     *
     * @return string
     *
     * @todo ensure we stacked the proper brand before calling
     */
    public function helpdeskUrl($path)
    {
        return $this->getBrandSetting('core.deskpro_url').ltrim($path, '/');
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
     * @param $path
     *
     * @return bool
     */
    public function isHelpdeskPath($path)
    {
        if (!preg_match('#^/[^/]#', $path)) {
            $pathinfo = @parse_url($path);
            if (!$pathinfo || !empty($pathinfo['host'])) {
                return false;
            }

            $path = $pathinfo['path'];
        }

        $path     = Strings::canonicalPath($path);
        $rootPath = '/'.trim($this->container->get('router')->generate('user', [], RouterInterface::ABSOLUTE_PATH), '/');

        if (!trim($path, '/') || strpos($path, $rootPath) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param array  $parameters
     *
     * @return mixed
     */
    public function urlFragment($name, array $parameters = [])
    {
        /** @var Router $router */
        $router = RouterUtils::unwrapDecoratedRouter($this->container->get('router'));

        return $router->getGenerator()->generateFragment($name, $parameters, false);
    }

    /**
     * @param array|FormView|object $displayArray
     * @param array                 $vars
     */
    public function renderCustomField($displayArray, array $vars = [])
    {
        if ($displayArray instanceof FormView) {
            return isset($displayArray->vars['rendered_data']) ? $displayArray->vars['rendered_data'] : null;
        }

        $handler = $displayArray['handler'];

        if (is_object($displayArray)) {
            $displayArray = $displayArray->toArray();
        }
        $vars = array_merge($displayArray, $vars);

        return $handler->renderHtml($displayArray['value'], $vars);
    }

    /**
     * @param array|FormView|object $displayArray
     * @param array                 $vars
     *
     * @throws \Twig_Error_Runtime
     *
     * @return string
     */
    public function renderCustomFieldForm($displayArray, array $vars = [])
    {
        if ($displayArray instanceof FormView) {
            $formExtension = $this->container->get('twig')->getExtension('form');

            return $formExtension->renderer->searchAndRenderBlock($displayArray, 'widget');
        }

        $handler  = $displayArray['handler'];
        $formView = $displayArray['formView'];

        if (is_object($displayArray)) {
            $displayArray = $displayArray->toArray();
        }
        $vars = array_merge($displayArray, $vars);

        return $handler->renderFormHtml($formView, $vars);
    }

    /**
     * @param array|FormView|object $displayArray
     * @param array                 $vars
     *
     * @return mixed
     */
    public function renderCustomFieldText($displayArray, array $vars = [])
    {
        if ($displayArray instanceof FormView) {
            return $displayArray->vars['rendered_data'];
        }

        $handler = $displayArray['handler'];

        if (is_object($displayArray)) {
            $displayArray = $displayArray->toArray();
        }
        $vars = array_merge($displayArray, $vars);

        return $handler->renderText($displayArray['value'], $vars);
    }

    /**
     * @param null $language
     *
     * @return mixed
     */
    public function getLanguageHtmlAttributes($language = null)
    {
        if (!($language instanceof Language)) {
            $language = $this->container->getTranslator()->getLanguage();
        }

        $attributes = [
            'dir'  => ($language->is_rtl ? 'dir="rtl"' : 'dir="ltr"'),
            'lang' => 'lang="'.htmlspecialchars(substr($language->locale, 0, 2), \ENT_QUOTES, 'UTF-8').'"',
        ];

        return implode(' ', $attributes);
    }

    /**
     * @param      $ltr
     * @param null $rtl
     * @param null $language
     *
     * @return null|string
     */
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

        if (!($language instanceof Language)) {
            $language = $this->container->getTranslator()->getLanguage();
        }

        if ($language->is_rtl) {
            return $rtl;
        } else {
            return $ltr;
        }
    }

    /**
     * @param null $language
     *
     * @return bool
     */
    public function isRtl($language = null)
    {
        if (!($language instanceof Language)) {
            $language = $this->container->getTranslator()->getLanguage();
        }

        return $language->is_rtl;
    }

    /**
     * @param       $phraseName
     * @param array $vars
     *
     * @return mixed
     */
    public function getPhraseDev($phraseName, array $vars = [])
    {
        return $this->container->get('deskpro.core.translate')->replaceVarsInString($phraseName, $vars);
    }

    /**
     * @param $phraseName
     *
     * @return mixed
     */
    public function hasPhrase($phraseName)
    {
        return $this->container->get('deskpro.core.translate')->hasPhrase($phraseName);
    }

    /**
     * @param $phraseName
     *
     * @return mixed
     */
    public function getPhraseText($phraseName)
    {
        $p = $this->container->get('deskpro.core.translate')->getPhraseText($phraseName);

        return $p;
    }

    /**
     * @param      $context
     * @param      $phraseName
     * @param null $vars
     * @param bool $raw
     *
     * @return mixed
     */
    public function getPhrase($context, $phraseName, $vars = null, $raw = false)
    {
        if (!$vars || !is_array($vars)) {
            $vars = [];
        }

        if (!$raw) {
            foreach ($vars as &$v) {
                $v = htmlspecialchars($v, \ENT_QUOTES, 'UTF-8');
            }
        }

        $vars['_context'] = $context;

        return $this->container->get('deskpro.core.translate')->phrase($phraseName, $vars);
    }

    /**
     * @param      $phraseName
     * @param null $property
     *
     * @return mixed
     */
    public function getPhraseObject($phraseName, $property = null)
    {
        return $this->container->get('deskpro.core.translate')->getPhraseObject($phraseName, $property);
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->container->isDebug();
    }

    /**
     * @param string $string
     *
     * @return mixed
     */
    public function getMd5($string)
    {
        return md5($string);
    }

    /**
     * @param $location
     *
     * @throws \Throwable
     *
     * @return string
     *
     * @todo ensure we stacked the proper brand before calling
     */
    public function assetFull($location)
    {
        /** @var AssetsHelper $assetHelper */
        $assetHelper = $this->container->get('templating.helper.assets');
        $assetUrl    = $assetHelper->getUrl($location);

        if (!preg_match('#^https?://#', $assetUrl)) {
            $url      = $this->getBrandSetting('core.deskpro_url');
            $url      = trim(str_replace('/index.php', '', $url), '/');
            $assetUrl = $url.$assetUrl;
        }

        return $assetUrl;
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    public function rawUrlEncode($str)
    {
        return rawurlencode($str);
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    public function strUpper($str)
    {
        return Strings::utf8_strtoupper($str);
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    public function strLower($str)
    {
        return Strings::utf8_strtolower($str);
    }

    /**
     * @param $hex
     *
     * @return array|bool
     */
    public function hex2rgb($hex)
    {
        $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
        $rgb = [];
        if (strlen($hex) == 6) {
            $colorVal     = hexdec($hex);
            $rgb['red']   = 0xFF & ($colorVal >> 0x10);
            $rgb['green'] = 0xFF & ($colorVal >> 0x8);
            $rgb['blue']  = 0xFF & $colorVal;
        } elseif (strlen($hex) == 3) {
            $rgb['red']   = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $rgb['green'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $rgb['blue']  = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            return false;
        }

        return $rgb;
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
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

    /**
     * @param $path
     *
     * @return string
     */
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

        return file_get_contents($path);
    }

    /**
     * @param            $path
     * @param array|null $with
     *
     * @return string
     */
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

    /**
     * @param $var
     *
     * @return string
     */
    public function dumpVar($var)
    {
        return \DpSys\LowError\SystemErrorHandler::varToString($var);
    }

    /**
     * @param      $url
     * @param bool $trimAdv
     *
     * @return mixed
     */
    public function urlTrimScheme($url, $trimAdv = false)
    {
        $ret = preg_replace('#^https?://#i', '', $url);

        if ($trimAdv) {
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

    /**
     * @param $code
     *
     * @return string
     */
    public function countryName($code)
    {
        $name = Countries::getCountryFromCode($code);
        if (!$name) {
            return $code;
        }

        return $name;
    }

    /**
     * @param string $interface
     *
     * @return string
     */
    public function getJsSsoLoader($interface = 'user')
    {
        ///////////////////////////////////////////////////////////////////////
        // Settings
        $person      = App::getCurrentPerson();
        $isFirstPage = $this->container->getSession()->isFirstPage(); // not to be trusted
        /** @var \Application\DeskPRO\Auth\AuthSettings $authSettings */
        $authSettings          = $this->container->getSystemService('auth_settings');
        $authInterfaceSettings = $interface == 'user' ? $authSettings->getUserInterfaceSettings() : $authSettings->getAgentInterfaceSettings();
        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = $this->getContainer()->get('request_stack');

        ///////////////////////////////////////////////////////////////////////
        // Ensure GET request
        if (!$request = $requestStack->getCurrentRequest()) {
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
        if ($authInterfaceSettings->isBackgroundSsoEnabled()) {
            $adapter = $authInterfaceSettings->getSsoAuthAdapter(SsoLoginActionInterface::CONTEXT_BACKGROUND);

            if ($adapter instanceof IframeSsoInterface) {
                $vars = array_merge(
                    [
                        'iframe_url' => '',
                        'render'     => true,
                    ],
                    $adapter->getIframeTemplateParams($isFirstPage)
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
        $legacyOutput = $this->legacyMagentoPluginCode($interface, $person, $isFirstPage);

        return $iFrameOutput.$legacyOutput;
    }

    /**
     * @param $interface
     * @param $person
     * @param $isFirstPage
     *
     * @return array
     */
    protected function legacyMagentoPluginCode($interface, $person, $isFirstPage)
    {
        /** @var \Application\DeskPRO\Usersource\UsersourceManager $usManager */
        $usManager = $this->container->getSystemService('usersource_manager');
        $sources   = $usManager->getAll()->forInterface($interface)->withCapability(
            UsersourceInfo::CAPABILITY_SSO_JS
        );
        $output = [];
        foreach ($sources as $source) {
            /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $factory */
            $factory = $this->container->getSystemService('usersource_auth_adapter_factory');
            $adapter = $factory->getAuthAdapter($source, SsoLoginActionInterface::CONTEXT_BACKGROUND);

            if ($adapter instanceof JsSsoInterface) {
                $output[] = $adapter->getSsoHtmlLoaderOutput($source, $this, $person, $isFirstPage);
            }
        }

        return implode("\n\n", $output);
    }

    /**
     * @return string
     */
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

    /**
     * @param string $str
     *
     * @return int
     */
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

    /**
     * @return mixed
     */
    public function min()
    {
        $args = func_get_args();

        return call_user_func_array('min', $args);
    }

    /**
     * @return mixed
     */
    public function max()
    {
        $args = func_get_args();

        return call_user_func_array('max', $args);
    }

    /**
     * @param $content
     *
     * @return mixed
     */
    public function plain_template_filter($content)
    {
        $content = RegexUtils::safePregReplace('#<\s*script#i', '<deskpro_script', $content);
        $content = RegexUtils::safePregReplace('#<\s*/\s*script#i', '</deskpro_script', $content);

        return $content;
    }

    /**
     * @param string $str
     * @param        $regex
     *
     * @return bool|int
     */
    public function match($str, $regex)
    {
        $regex = Strings::getInputRegexPattern($regex);
        if (!$regex) {
            return false;
        }

        return RegexUtils::safePregMatch($regex, $str);
    }

    /**
     * @param $context
     * @param $k
     * @param $v
     */
    public function set_tplvar($context, $k, $v)
    {
        if (!isset($context['tplvars'])) {
            $context['tplvars'] = new \stdClass();
        }

        $context['tplvars']->$k = $v;

        return;
    }

    /**
     * @param        $id
     * @param string $name
     *
     * @return string
     */
    public function getTplSourceTemplate($id, $name)
    {
        $source = $this->container->getTemplating()->getSource($name);
        $source = str_replace('<script>', '%startScript%', $source);
        $source = str_replace('</script>', '%endScript%', $source);
        $source = '<script type="text/x-deskpro-tmpl" id="'.$id.'">'.$source.'</script>';

        return $source;
    }

    /**
     * @param $var
     *
     * @return string
     */
    public function ngVar($var)
    {
        return '{{'.$var.'}}';
    }

    /**
     * @param $phraseName
     *
     * @return mixed
     */
    public function ngPluralPhrase($phraseName)
    {
        $positions = [];

        for ($i = 0; $i < 5; ++$i) {
            $text = $this->container->getTranslator()->getPhraseTextCount($phraseName, $i);
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

    /**
     * @param $var
     *
     * @return string
     */
    public function ngBind($var)
    {
        return '<span ng-bind="'.htmlspecialchars($var).'"></span>';
    }

    /**
     * @param $var
     *
     * @return string
     */
    public function ngStaticVar($var)
    {
        return '<span bo-bind="'.htmlspecialchars($var).'"></span>';
    }

    /**
     * @param      $context
     * @param      $tplName
     * @param null $saveName
     *
     * @return string
     */
    public function ngIncTpl($context, $tplName, $saveName = null)
    {
        $name = $tplName;

        $tpl = $this->container->getTemplating();
        if (!$tpl->exists($name)) {
            return '<!-- No such template exists: '.$name.' -->';
        }

        $rendered = $tpl->render($name, $context);

        if (!$saveName) {
            $saveName = $tplName;
            $saveName = preg_replace('#^(AdminInterface|Admin|Agent)Bundle:#', '$1/', $saveName);
            $saveName = str_replace(':', '/', $saveName);
            $saveName = preg_replace('#\.twig$#', '', $saveName);
        }

        if (strpos($rendered, '<script') !== false) {
            // If it has inner script tags we should be using our special dp-ng-template tag and encode the tpl as json
            $json = \Application\DeskPRO\Util::jsonEncode(['template' => $rendered]);
            $html = '<script type="text/dp-ng-template" id="'.$saveName.'">'.$json.'</script>';
        } else {
            $html = '<script type="text/ng-template" id="'.$saveName.'">'.$rendered.'</script>';
        }

        return $html;
    }

    /**
     * @param        $route
     * @param string $params
     *
     * @return string
     */
    public function ngHref($route, $params = '{}')
    {
        if (is_array($params)) {
            $params = json_encode($params);
        }

        return '{{ state_path(\''.addslashes($route).'\', '.$params.') }}';
    }

    /**
     * @param        $routeVar
     * @param string $params
     *
     * @return string
     */
    public function ngHrefVar($routeVar, $params = '{}')
    {
        return '{{ state_path('.$routeVar.', '.str_replace(["'", '"'], ['&apos;', '&quot;'], $params).') }}';
    }

    /**
     * @param string $string
     * @param int    $len
     * @param null   $break
     *
     * @return string
     */
    public function smartWrap($string, $len = 50, $break = null)
    {
        if ($break === null) {
            $break = Strings::ZERO_WIDTH_SPACE;
        }

        return Strings::smartWordWrap($string, $len, $break);
    }

    /**
     * @param $baseId
     * @param $locName
     *
     * @return string
     */
    public function getDpAppLocation($baseId, $locName)
    {
        $locId = preg_replace('#[^a-zA-Z0-9_]#', '_', $locName);

        return '<div id="'.$baseId.'_'.$locId.'" class="dp-app-context-container as-default-hidden" data-location-name="'.$locName.'"></div>';
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function jsonEncodeInHtml($data)
    {
        return \Application\DeskPRO\Util::jsonEncode($data);
    }

    /**
     * @param string $string
     * @param        $length
     *
     * @return mixed
     */
    public function textWrapMarks($string, $length)
    {
        // Inserts a 0-width space at position $length
        // Browsers will wrap at this point in long strings
        return RegexUtils::safePregReplace('/(.{'.$length.'})/u', '$1'.Strings::chrUtf8(8203), $string);
    }

    /**
     * @param     $string
     * @param     $regex
     * @param     $replace
     * @param int $limit
     *
     * @return mixed
     */
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

    /**
     * @param $what
     *
     * @return bool
     */
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

    /**
     * @param       $loc
     * @param array $options
     *
     * @return string
     */
    public function js_error_tracking($loc, array $options = [])
    {
        if ($this->getContainer()->isDebug()) {
            if (!defined('DP_USE_JS_LOGGER')) {
                return '';
            }
        }

        $sid = '';
        if ($this->getContainer()->isDebug()) {
            $sid .= 'DEV-';
        }
        if (defined('DP_BUILD_TIME')) {
            $sid .= '#'.DP_BUILD_TIME.'-';
        } else {
            $sid .= '#0-';
        }
        if (defined('DP_REQUEST_ID')) {
            $sid .= DP_REQUEST_ID;
        } else {
            $sid .= 'unknown';
        }

        $version = defined('DP_BUILD_TIME') ? DP_BUILD_TIME : '0';

        /** @var \Symfony\Component\Asset\Packages $helper */
        $helper = $this->getContainer()->get('assets.packages');

        $src = $helper->getUrl('vendor/trackjs/tracker.js');

        $html = <<<HTML
<script type="text/javascript">
window.onerror = function () {};
window.onerror = null;
window._trackJs = {
    sessionId: '$sid',
    token: '4eebe4aa1bc2404e89fc4250152d18a0',
    version: '$version',
    console: { enabled: true, display: true, error: true },
    network: { error: false }
};
</script>
<script type="text/javascript" src="$src" data-token="4eebe4aa1bc2404e89fc4250152d18a0"></script>
HTML;

        return $html;
    }

    /**
     * @param                          $string
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param array|null               $extraVars
     *
     * @throws null
     *
     * @return null|string
     */
    public function renderTicketTemplate($string, Ticket $ticket, ExecutorContextInterface $context, array $extraVars = null)
    {
        // Simple string, cant be a template so dont waste time evaluating it
        if (strpos($string, '{{') === false && strpos($string, '{%') === false) {
            return $string;
        }

        $vars = [
            'performer'     => $context->getPersonContext(),
            'ticket'        => $ticket,
            'helpdesk_name' => $this->getContainer()->getBrandSetting('core.deskpro_name'),
            'site_name'     => $this->getContainer()->getBrandSetting('core.site_name'),
            'user_vars'     => $context->getUserVars(),
        ];

        if ($extraVars) {
            $vars = array_merge($vars, $extraVars);
        }

        try {
            $rendered = $this->getContainer()->getTwig()->renderStringTemplate($string, $vars);
        } catch (\Exception $e) {
            return $string;
        }

        return $rendered;
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
}
