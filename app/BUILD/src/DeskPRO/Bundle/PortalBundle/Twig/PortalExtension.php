<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\Entity;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;

/**
 * Class PortalExtension.
 */
class PortalExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandStack
     */
    public function getBrandStack()
    {
        return $this->container->get('brand_stack');
    }

    /**
     * @return \Application\DeskPRO\NewSettings\SettingsResolver
     */
    public function getSettingsResolver()
    {
        return $this->container->get('settings_resolver');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Content\AvatarResolver
     */
    public function getAvatarResolver()
    {
        return $this->container->get('avatar_resolver');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Helper\TicketPublicIdResolver
     */
    public function getTicketPublicIdResolver()
    {
        return $this->container->get('ticket.public_id_resolver');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager
     */
    public function getPermissionManager()
    {
        return $this->container->get('portal_permissions_manager');
    }

    /**
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Helper\PortalRatingsHelper
     */
    public function getRatingsHelper()
    {
        return $this->container->get('ratings_helper');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider
     */
    public function getVisitorIdentificationProvider()
    {
        return $this->container->get('visitor_identification_provider');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    public function getLanguageManager()
    {
        return $this->container->get('language_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('auth_usersources_js_object', [$this, 'getAuthUsersourcesJsObject'], ['is_safe' => ['html', 'javascript']]),
            new \Twig_SimpleFunction('ticket_status', [$this, 'getTicketStatusString']),
            new \Twig_SimpleFunction('ticket_public_id', [$this, 'getPublicTicketId']),
            new \Twig_SimpleFunction('brand_setting', [$this, 'getBrandSetting'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('brand', [$this, 'getBrand']),
            new \Twig_SimpleFunction('avatar_url', [$this, 'getAvatarUrl']),
            new \Twig_SimpleFunction('render_message', [$this, 'getRenderedObject'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_news', [$this, 'getRenderedObject'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_secure_content_cats', [$this, 'getSecureCats']),
            new \Twig_SimpleFunction('user_up_voted', [$this, 'didUserUpVote']),
            new \Twig_SimpleFunction('user_down_voted', [$this, 'didUserDownVote']),
            new \Twig_SimpleFunction('file_icon', [$this, 'makeFileIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('article_icon', [$this, 'makeArticleIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('news_icon', [$this, 'makeNewsIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('feedback_icon', [$this, 'makeFeedbackIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('content_icon', [$this, 'makeContentIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('ticket_view', [$this, 'getTicketView']),
            new \Twig_SimpleFunction('phrase_form_error', [$this, 'makeFormError'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('insert_glossary_js', [$this, 'makeGlossaryJs'], ['is_safe' => ['html', 'javascript']]),
            new \Twig_SimpleFunction('portal_mode', [$this, 'getPortalMode'], ['is_safe' => ['html', 'javascript']]),
            new \Twig_SimpleFunction('is_portal_widget_enabled', [$this, 'isPortalWidgetEnabled']),
            new \Twig_SimpleFunction('portal_widget_options', [$this, 'getPortalWidgetOptions']),
            new \Twig_SimpleFunction('minified_widget_loader', [$this, 'getMinifiedWidgetLoader']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            // Copied from legacy templating, its used to render custom field values (eg for templates)
            new \Twig_SimpleFilter('smart_wrap', function ($string, $len = 50, $break = null) {
                if ($break === null) {
                    $break = Strings::ZERO_WIDTH_SPACE;
                }

                return Strings::smartWordWrap($string, $len, $break);
            }),
        ];
    }

    /**
     * @return string
     */
    public function getAuthUsersourcesJsObject()
    {
        $usersources = $this->container->get('usersources_view_helper')->createUsersourceViewList();

        return json_encode($usersources);
    }

    /**
     * @param $article
     *
     * @return string
     */
    public function makeGlossaryJs($article)
    {
        $glossary       = new \Application\DeskPRO\Publish\GlossaryHandler($this->container->get('doctrine.orm.default_entity_manager'));
        $glossary_words = $glossary->findWords($article->content);
        $word_defs      = $glossary->getWordDefs($glossary_words);

        $dp_glossary_words = [];
        foreach ($glossary_words as $word) {
            $dp_glossary_words[$word] = $word_defs[$word];
        }

        $data = [
            'words' => $glossary_words,
            'defs'  => $dp_glossary_words,
        ];

        $data_encoded = json_encode($data);

        $script = '<script type="text/javascript">window.DP_ARTICLE_GLOSSARY = '.$data_encoded.';</script>';

        return $script;
    }

    /**
     * @param FormError $form_error
     *
     * @return string
     */
    public function makeFormError(FormError $form_error)
    {
        $params = $this->parseErrorParams($form_error->getMessageParameters());

        return $this->getLanguageManager()->phrase($form_error->getMessageTemplate(), $params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function parseErrorParams(array $params)
    {
        $cleaned_params = [];

        foreach ($params as $raw_name => $param) {
            // strip symfony's curly braces
            if (substr($raw_name, 0, 2) === '{{') {
                $raw_name = substr($raw_name, 2);
                $raw_name = substr($raw_name, 0, -2);
            }
            $clean_name                  = trim($raw_name);
            $cleaned_params[$clean_name] = $param;
        }

        return $cleaned_params;
    }

    /**
     * @param $content
     *
     * @return string
     */
    public function makeContentIcon($content)
    {
        return $this->container->get('icon_factory')->makeContentIcon($content);
    }

    /**
     * @param $blob_or_download
     *
     * @return string
     */
    public function makeFileIcon($blob_or_download)
    {
        return $this->container->get('icon_factory')->makeFileIcon($blob_or_download);
    }

    /**
     * @param $article
     *
     * @return string
     */
    public function makeArticleIcon($article)
    {
        return $this->container->get('icon_factory')->makeArticleIcon($article);
    }

    /**
     * @param $news
     *
     * @return string
     */
    public function makeNewsIcon($news)
    {
        return $this->container->get('icon_factory')->makeNewsIcon($news);
    }

    /**
     * @param $feedback
     *
     * @return string
     */
    public function makeFeedbackIcon($feedback)
    {
        return $this->container->get('icon_factory')->makeFeedbackIcon($feedback);
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function didUserUpVote($object)
    {
        if ($rating = $this->getRating($object)) {
            return $rating->isPositive();
        }

        return false;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function didUserDownVote($object)
    {
        if ($rating = $this->getRating($object)) {
            return $rating->isNegative();
        }

        return false;
    }

    /**
     * @param $object
     *
     * @return Entity\Rating|null
     */
    public function getRating($object)
    {
        $person = $this->getPerson();

        if ($person instanceof Entity\Person && !$person instanceof PersonGuest) {
            if ($rating = $this->getRatingsHelper()->findPersonRating($object, $person)) {
                return $rating;
            }
        } else {
            $visitor_id = $this->getVisitorIdentificationProvider()->getVisitorIdentifier();

            if ($rating = $this->getRatingsHelper()->findVisitorRating($object, $visitor_id)) {
                return $rating;
            }
        }

        return;
    }

    /**
     * @param Entity\Ticket $ticket
     *
     * @return TicketView
     */
    public function getTicketView(Entity\Ticket $ticket)
    {
        return $this->container->get('tickets.view')->getUserTicketView($ticket);
    }

    /**
     * @param $ticket
     *
     * @return int
     */
    public function getPublicTicketId($ticket)
    {
        if ($ticket instanceof TicketView) {
            $ticket = $ticket->getTicket();
        }

        if (!$ticket instanceof Entity\Ticket) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Twig function "ticket_public_id" requires a Ticket or TicketView, but "" given',
                    is_object($ticket) ? get_class($ticket) : 'scalar'
                )
            );
        }

        return $this->getTicketPublicIdResolver()->findId($ticket);
    }

    /**
     * @param Entity\ContentAbstract $content
     *
     * @return array
     */
    public function getSecureCats(Entity\ContentAbstract $content)
    {
        $permission_bag = $this->getPermissionBagForCurrentUser();

        if ($content instanceof Entity\Article) {
            $cat = $content->getPrimaryCategory();
            if (!$permission_bag->hasContentCategoryAccess($cat)) {
                foreach ($content->getCategories() as $cat) {
                    if ($permission_bag->hasContentCategoryAccess($cat)) {
                        break;
                    }
                }
            }
        } elseif ($content instanceof Entity\News) {
            $cat = $content->getCategory();
        } elseif ($content instanceof Entity\Download) {
            $cat = $content->getCategory();
        } elseif ($content instanceof Entity\Feedback) {
            $cat = $content->getCategory();
        } else {
            throw new \InvalidArgumentException('the get_secure_cats twig function requires one of: Article, Download, News, Feedback, but did not get one');
        }

        $category_tree = [];

        $permission_bag = $this->getPermissionBagForCurrentUser();
        foreach ($cat->getTreeParents() as $c) {
            if ($permission_bag->hasContentCategoryAccess($c)) {
                $category_tree[] = $c;
            }
        }

        // check here, too
        if ($permission_bag->hasContentCategoryAccess($cat)) {
            $category_tree[] = $cat;
        }

        return $category_tree;
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
     * @param string $prop
     *
     * @return string
     */
    public function getBrand($prop)
    {
        return $this->getBrandStack()->getActive()->getBrand()->get($prop);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Mode\PortalMode
     */
    public function getPortalMode()
    {
        return $this->container->get('portal_mode_storage')->getMode();
    }

    /**
     * @param Entity\Ticket $ticket
     *
     * @return string
     */
    public function getTicketStatusString($ticket)
    {
        switch ($ticket->status_code) {
            case Entity\Ticket::STATUS_RESOLVED:
                return 'Resolved';
            case Entity\Ticket::STATUS_AWAITING_AGENT:
                return 'Awaiting Agent';
            case Entity\Ticket::STATUS_AWAITING_USER:
                return 'Awaiting You';
            case Entity\Ticket::STATUS_HIDDEN:
                return 'Hidden';
            case Entity\Ticket::STATUS_ARCHIVED:
                return 'Archived';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get URL to a profile picture/avatar.
     *
     * @param mixed $obj
     * @param int   $size
     *
     * @return string the url
     */
    public function getAvatarUrl($obj = null, $size = 80)
    {
        return $this->getAvatarResolver()->getAvatar($obj, $size);
    }

    /**
     * @param mixed $obj
     * @param mixed $opt
     *
     * @return string
     */
    public function getRenderedObject($obj, $opt = null)
    {
        if ($obj instanceof Entity\TicketMessage) {
            return $this->container->get('ticket_message.renderer')->render($obj);
        } elseif ($obj instanceof Entity\News) {
            if ($opt == 'exceprt') {
                return $this->container->get('news.renderer')->renderExceprt($obj);
            } else {
                return $this->container->get('news.renderer')->render($obj);
            }
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return [
            'global_settings' => $this->getSettingsResolver()->getGlobalSettings(),
            'language'        => $this->getLanguageManager()->getLanguageStack()->getActive(),
        ];
    }

    /**
     * @return bool
     */
    public function isPortalWidgetEnabled()
    {
        return $this->container->get('widget_settings_resolver')->isEnabledOnPortal();
    }

    /**
     * @return array
     */
    public function getPortalWidgetOptions()
    {
        $widget_settings = $this->container->get('widget_settings_resolver');

        return array_merge($widget_settings->getPortalBrandSettings(), [
            'company' => $widget_settings->getCompanySettings(),
        ]);
    }

    /**
     * @param string $root_path
     * @param string $widget_bundle_path
     *
     * @return string
     */
    public function getMinifiedWidgetLoader($root_path, $widget_bundle_path)
    {
        $asset_dir          = $this->container->get('deskpro.app_env')->getAppWwwAssetDir();
        $widget_loader_path = $asset_dir.'/pub/build/widget_loader.min.js';

        if (file_exists($widget_loader_path)) {
            $widget_loader = file_get_contents($widget_loader_path);

            // override options
            $widget_loader = str_replace('__DP_APP_SRC__', '"'.$widget_bundle_path.'"', $widget_loader);
            $widget_loader = str_replace('__DP_URL__', '"'.$root_path.'"', $widget_loader);
            $widget_loader = str_replace('__DP_OPTIONS__', json_encode($this->getPortalWidgetOptions()), $widget_loader);

            return $widget_loader;
        }

        return '';
    }

    /**
     * @return PersonGuest|Entity\Person
     */
    protected function getPerson()
    {
        $person = null;
        $token  = $this->getTokenStorage()->getToken();

        if ($token) {
            $person = $token->getUser();
        }
        if (!$person instanceof Entity\Person) {
            $person = new PersonGuest();
        }

        return $person;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsBag
     */
    protected function getPermissionBagForCurrentUser()
    {
        $person = $this->getPerson();
        if ($person) {
            return $this->getPermissionManager()->getPermissionsBagForPerson($person);
        }

        return $this->getPermissionManager()->getPermissionsBagForGuest();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'portal_extension';
    }
}
