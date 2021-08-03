<?php

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Publish\GlossaryHandler;
use Application\DeskPRO\ResourceScanner\AdvancedSettings;
use DeskPRO\Bundle\AppBundle\Entity\HasIconProperty;
use DeskPRO\Bundle\AppBundle\Entity\HasSplashImageProperty;
use DeskPRO\Bundle\AppBundle\Entity\IconProperty;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandSettings;
use DeskPRO\Bundle\MessengerBundle\Service\MessengerSettingsResolver;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings;
use DeskPRO\Component\Filesystem\SafeFile;
use Exception;
use Orb\Data\ContentTypes;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;

/**
 * Class PortalExtension.
 */
class PortalExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var DeskproContainer
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
     * @throws \Throwable
     *
     * @return \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    public function getBrandStack()
    {
        return $this->container->getBrandStack();
    }

    /**
     * @throws \Exception
     *
     * @return \Application\DeskPRO\NewSettings\SettingsResolver
     */
    public function getSettingsResolver()
    {
        return $this->container->get('settings_resolver');
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Content\AvatarResolver
     */
    public function getAvatarResolver()
    {
        return $this->container->get('avatar_resolver');
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Helper\TicketPublicIdResolver
     */
    public function getTicketPublicIdResolver()
    {
        return $this->container->get('ticket.public_id_resolver');
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager
     */
    public function getPermissionManager()
    {
        return $this->container->get('portal_permissions_manager');
    }

    /**
     * @throws \Exception
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\PortalBundle\Helper\PortalRatingsHelper
     */
    public function getRatingsHelper()
    {
        return $this->container->get('ratings_helper');
    }

    /**
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider
     */
    public function getVisitorIdentificationProvider()
    {
        return $this->container->get('visitor_identification_provider');
    }

    /**
     * @throws \Exception
     *
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
            new \Twig_SimpleFunction('has_login_form', [$this, 'hasLoginForm']),
            new \Twig_SimpleFunction('is_forgot_password_visible', [$this, 'isForgotPasswordVisible']),
            new \Twig_SimpleFunction('ticket_status', [$this, 'getTicketStatusString']),
            new \Twig_SimpleFunction('ticket_public_id', [$this, 'getPublicTicketId']),
            new \Twig_SimpleFunction('brand_setting', [$this, 'getBrandSetting'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('brand', [$this, 'getBrand']),
            new \Twig_SimpleFunction('avatar_url', [$this, 'getAvatarUrl']),
            new \Twig_SimpleFunction('avatar_default_url', [$this, 'getAvatarDefaultUrl']),
            new \Twig_SimpleFunction('hc_avatar', [$this, 'getHelpcenterAvatar'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_message', [$this, 'getRenderedObject'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_news', [$this, 'getRenderedObject'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_secure_content_cats', [$this, 'getSecureCats']),
            new \Twig_SimpleFunction('user_up_voted', [$this, 'didUserUpVote']),
            new \Twig_SimpleFunction('user_down_voted', [$this, 'didUserDownVote']),
            new \Twig_SimpleFunction('file_icon', [$this, 'makeFileIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('article_icon', [$this, 'makeArticleIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('news_icon', [$this, 'makeNewsIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('community_icon', [$this, 'makeCommunityIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('content_icon', [$this, 'makeContentIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('helpcenter_content_icon', [$this, 'makeHelpCenterContentIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('content_fa_icon', [$this, 'getFaIconClass']),
            new \Twig_SimpleFunction('ticket_view', [$this, 'getTicketView']),
            new \Twig_SimpleFunction('ticket_excerpts', [$this, 'getTicketExcerpts']),
            new \Twig_SimpleFunction('phrase_form_error', [$this, 'makeFormError'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('insert_glossary_js', [$this, 'makeGlossaryJs'], ['is_safe' => ['html', 'javascript']]),
            new \Twig_SimpleFunction('portal_mode', [$this, 'getPortalMode'], ['is_safe' => ['html', 'javascript']]),
            new \Twig_SimpleFunction('is_portal_widget_enabled', [$this, 'isPortalWidgetEnabled']),
            new \Twig_SimpleFunction('is_portal_messenger_enabled', [$this, 'isPortalMessengerEnabled']),
            new \Twig_SimpleFunction('is_widget_jwt_enabled', [$this, 'isWidgetJwtEnabled']),
            new \Twig_SimpleFunction('create_widget_jwt_token', [$this, 'createWidgetJwtToken']),
            new \Twig_SimpleFunction('portal_widget_loader', [$this, 'getWidgetLoader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('should_show_nav_buttons', [$this, 'shouldShowNavButtons']),
            new \Twig_SimpleFunction('can_login', [$this, 'canLogin']),
            new \Twig_SimpleFunction('category_color_css', [$this, 'categoryColorCss']),
            new \Twig_SimpleFunction('render_icon_from', [$this, 'renderIconFrom'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('render_icon', [$this, 'renderIcon'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('highlight_text', [$this, 'highlightText'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('has_splash', [$this, 'hasSplashImage']),
            new \Twig_SimpleFunction('get_splash_url', [$this, 'getSplashUrl']),
            new \Twig_SimpleFunction('get_splash_bgcss', [$this, 'getSplashBgcss'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('get_user', [$this, 'getPerson']),
            new \Twig_SimpleFunction('current_theme', [$this, 'getCurrentTheme']),
            new \Twig_SimpleFunction('agent_can_edit', [$this, 'agentCanEdit']),
            new \Twig_SimpleFunction('asset_data_url', [$this, 'getAssetDataUrl']),
            new \Twig_SimpleFunction('is_category_subscribed', [$this, 'isCategorySubscribed']),
            new \Twig_SimpleFunction('core_deskpro_name', [$this, 'getDeskproName']),
            new \Twig_SimpleFunction('widget_phrases_json', [$this, 'getWidgetPhrasesJson']),

            // Copied from legacy templating, used to render notification rows
            new \Twig_SimpleFunction('has_phrase', [$this, 'hasPhrase'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('html_content_preview', [$this, 'getHtmlContentPreview']),

            // Copied from legacy templating, its used to render custom field values (eg for templates)
            new \Twig_SimpleFilter('smart_wrap', function ($string, $len = 50, $break = null) {
                if ($break === null) {
                    $break = Strings::ZERO_WIDTH_SPACE;
                }

                return Strings::smartWordWrap($string, $len, $break);
            }),

            new \Twig_SimpleFilter('safe_link_urls', [$this, 'safeLinkUrls'], ['is_safe' => ['html']]),
        ];
    }

    // legacy

    /**
     * @param $phraseName
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasPhrase($phraseName)
    {
        return $this->container->get('deskpro.core.translate')->hasPhrase($phraseName);
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getAuthUsersourcesJsObject()
    {
        return json_encode($this->container->get('usersources_view_helper')->createUsersourceViewList());
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function hasLoginForm()
    {
        return $this->container->get('usersources_view_helper')->hasLoginForm();
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function isForgotPasswordVisible()
    {
        return $this->container->get('dp_authentication_manager.user')->isForgotPasswordVisible();
    }

    /**
     * @param $article
     *
     * @throws \Throwable
     *
     * @return string
     */
    public function makeGlossaryJs($article)
    {
        $brand         = $this->container->getBrandStack()->getActive()->getBrand();
        $glossary      = new GlossaryHandler($this->container->getEm(), $brand);
        $glossaryWords = $glossary->findWords($article->content);
        $wordDefs      = $glossary->getWordDefs($glossaryWords);

        $dpGlossaryWords = [];
        foreach ($glossaryWords as $word) {
            $dpGlossaryWords[$word] = $wordDefs[$word];
        }

        $data = [
            'words' => $glossaryWords,
            'defs'  => $dpGlossaryWords,
        ];

        $dataEncoded = json_encode($data);

        $script = '<script type="text/javascript">window.DP_ARTICLE_GLOSSARY = '.$dataEncoded.';</script>';

        return $script;
    }

    /**
     * @param FormError $formError
     *
     * @throws \Exception
     *
     * @return string
     */
    public function makeFormError(FormError $formError)
    {
        $messageFactory = $this->container->get('form_error.message_factory.portal');
        $codeFactory    = $this->container->get('form_error.code_factory');

        $code    = $codeFactory->getErrorCodeForFormError($formError);
        $message = $messageFactory->createFormErrorMessage($code, $formError);
        if (is_string($message)) {
            $message = htmlentities($message);
        }

        return $message;
    }

    /**
     * @param $content
     *
     * @throws \Exception
     *
     * @return string
     */
    public function makeContentIcon($content)
    {
        return $this->container->get('icon_factory')->makeContentIcon($content);
    }

    /**
     * @param $content
     *
     * @return string
     */
    public function makeHelpCenterContentIcon($content)
    {
        return $this->container->get('icon_factory')->makeContentIcon($content, true);
    }

    public function getFaIconClass($content)
    {
        return $this->container->get('icon_factory')->getFaClassForContent($content);
    }

    /**
     * @param $blobOrDownload
     *
     * @throws \Exception
     *
     * @return string
     */
    public function makeFileIcon($blobOrDownload)
    {
        return $this->container->get('icon_factory')->makeFileIcon($blobOrDownload);
    }

    /**
     * @param $article
     *
     * @throws \Exception
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
     * @throws \Exception
     *
     * @return string
     */
    public function makeNewsIcon($news)
    {
        return $this->container->get('icon_factory')->makeNewsIcon($news);
    }

    /**
     * @param $topic
     *
     * @throws \Exception
     *
     * @return string
     */
    public function makeCommunityIcon($topic)
    {
        return $this->container->get('icon_factory')->makeCommunityTopicIcon($topic);
    }

    /**
     * @param $object
     *
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception*
     *
     * @return Entity\Rating|null
     */
    public function getRating($object)
    {
        $person = $this->getPerson();

        if ($person instanceof Entity\Person && !$person instanceof PersonGuest) {
            if ($rating = $this->getRatingsHelper()->findPersonRating($object->getRatingData(), $person)) {
                return $rating;
            }
        } else {
            $visitorId = $this->getVisitorIdentificationProvider()->getVisitorIdentifier();

            if ($rating = $this->getRatingsHelper()->findVisitorRating($object->getRatingData(), $visitorId)) {
                return $rating;
            }
        }

        return null;
    }

    /**
     * @param Entity\Ticket $ticket
     *
     * @throws \Exception
     *
     * @return TicketView
     */
    public function getTicketView(Entity\Ticket $ticket)
    {
        return $this->container->get('tickets.view')->getUserTicketView($ticket, $this->getPerson());
    }

    /**
     * @param array $tickets
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getTicketExcerpts($tickets)
    {
        return $this->container->get('data.ticket_excerpt')->getTicketsLastReply($tickets);
    }

    /**
     * @param $ticket
     *
     * @throws \Exception
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
                    'Twig function "ticket_public_id" requires a Ticket or TicketView, but "%s" given',
                    is_object($ticket) ? get_class($ticket) : 'scalar'
                )
            );
        }

        return $this->getTicketPublicIdResolver()->findId($ticket);
    }

    /**
     * @param ContentAbstract $content
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getSecureCats(ContentAbstract $content)
    {
        $category       = $this->getContentCategory($content);
        $categoryTree   = [];
        $permissionsBag = $this->getPermissionBagForCurrentUser();

        if (!$category) {
            return [];
        }
        foreach ($category->getTreeParents() as $c) {
            if ($permissionsBag->hasContentCategoryAccess($c)) {
                $categoryTree[] = $c;
            }
        }

        // check here, too
        if ($permissionsBag->hasContentCategoryAccess($category)) {
            $categoryTree[] = $category;
        }

        return $categoryTree;
    }

    /**
     * @param Entity\ContentAbstract $content
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return Entity\ArticleCategory|Entity\DownloadCategory|Entity\FeedbackCategory|Entity\NewsCategory
     */
    protected function getContentCategory(Entity\ContentAbstract $content)
    {
        $permissionsBag = $this->getPermissionBagForCurrentUser();

        if ($content instanceof Entity\Article) {
            $category = $content->getPrimaryCategory();
            if (!$permissionsBag->hasContentCategoryAccess($category)) {
                foreach ($content->getCategories() as $category) {
                    if ($permissionsBag->hasContentCategoryAccess($category)) {
                        break;
                    }
                }
            }
        } elseif ($content instanceof Entity\News) {
            $category = $content->getCategory();
        } elseif ($content instanceof Entity\Download) {
            $category = $content->getCategory();
        } elseif ($content instanceof Entity\CommunityTopic) {
            $category = $content->getCategory();
        } else {
            throw new \InvalidArgumentException('the get_secure_cats twig function requires one of: Article, Download, News, Community Topics, but did not get one');
        }

        return $category;
    }

    /**
     * @param string $setting
     * @param mixed  $default
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    public function getBrandSetting($setting, $default = null)
    {
        if (!in_array($setting, AdvancedSettings::getAcceptableSettingIds())) {
            return $default;
        }

        return $this->getBrandStack()->getActive()->getSetting($setting, $default);
    }

    /**
     * @param string $prop
     *
     * @throws \Throwable
     *
     * @return string
     */
    public function getBrand($prop)
    {
        return $this->getBrandStack()->getActive()->getBrand()->get($prop);
    }

    /**
     * @throws \Exception
     *
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
        switch ($ticket->staus) {
            case TicketStatus::STATUS_TYPE_RESOLVED:
                return 'Resolved';
            case TicketStatus::STATUS_TYPE_AWAITING_AGENT:
                return 'Awaiting Agent';
            case TicketStatus::STATUS_TYPE_AWAITING_USER:
                return 'Awaiting You';
            case TicketStatus::STATUS_TYPE_HIDDEN:
                return 'Hidden';
            case TicketStatus::STATUS_TYPE_ARCHIVED:
                return 'Archived';
            case TicketStatus::STATUS_TYPE_PENDING:
                return 'Pending';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get URL to a profile picture/avatar.
     *
     * @param mixed $obj
     * @param int   $size
     * @param mixed $fallbackOnDefault
     *
     * @throws \Exception
     *
     * @return string the url
     */
    public function getAvatarUrl($obj = null, $size = 80, $fallbackOnDefault = true)
    {
        $returnedDefault = false;
        $avatar          = $this->getAvatarResolver()->getAvatar($obj, $size, $returnedDefault);
        if (!$fallbackOnDefault && $returnedDefault) {
            return null;
        }

        return $avatar;
    }

    /**
     * Get URL to a profile picture/avatar.
     *
     * @param mixed $obj
     * @param int   $size
     *
     * @return string the url
     */
    public function getAvatarDefaultUrl($obj = null, $size = 80)
    {
        return $this->getAvatarResolver()->getDefaultPersonAvatar($size);
    }

    /**
     * @param Entity\Person $obj
     * @param string $className
     * @param int $size
     * @param bool $hidden
     *
     * @throws Exception
     *
     * @return string
     */
    public function getHelpcenterAvatar($obj = null, $className = 'dp-po-avatar', $size = 80, $hidden = true)
    {
        $avatarUrl = $this->getAvatarUrl($obj, $size, false);
        if ($avatarUrl) {
            if ($className) {
                $attributes = 'class="'.$className.'-image"';
            } else {
                $attributes = 'class="dp-po-avatar-image"';
            }

            if ($hidden) {
                $attributes .= ' aria-hidden="true"';
            } else {
                $attributes .= ' aria-label="'.$obj->getDisplayName().'"';
            }

            return "<span $attributes style='background-image: url(\"$avatarUrl\");'></span>";
        }

        if ($obj) {
            if ($className) {
                $className = 'class="'.$className.'-name"';
            } else {
                $className = 'class="dp-po-avatar-name"';
            }
            $initials = $obj->getInitials();

            if ($hidden) {
                return "<span $className aria-hidden=\"true\">$initials</span>";
            }

            return "<span $className aria-hidden=\"true\">$initials</span><span class='sr-only'>".$obj->getDisplayName()."</span>";
        }
    }

    /**
     * @param mixed $obj
     * @param mixed $opt
     *
     * @throws \Exception
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
     * @throws \Exception
     *
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
     * @throws \Exception
     *
     * @return bool
     */
    public function isPortalWidgetEnabled()
    {
        return $this->container->get('widget_settings_resolver')->isEnabledOnPortal() && !$this->container->get('deskpro.feature_flags')->hasBeta('messenger');
    }

    /**
     * @throws \Throwable
     *
     * @return bool
     */
    public function isPortalMessengerEnabled()
    {
        $brand            = $this->getBrandStack()->getActive()->getBrand();
        $settingsResolver = $this->container->get('messenger.service.settings_resolver');

        /** @var MessengerSettings $messengerSettings */
        $messengerSettings = $settingsResolver->getMessengerSettings($brand);

        return $messengerSettings->getEmbed() && $messengerSettings->getEmbed()->isShowOnPortal() &&
            $this->container->get('deskpro.feature_flags')->hasBeta('messenger');
    }

    /**
     * @throws \Throwable
     *
     * @return bool
     */
    public function isWidgetJwtEnabled()
    {
        $brand            = $this->getBrandStack()->getActive()->getBrand();
        $settingsResolver = $this->container->get('messenger.service.settings_resolver');

        return $settingsResolver->getSettings(MessengerSettingsResolver::JWT_SECRET, $brand, false);
    }

    /**
     * @param $person
     *
     * @throws Exception
     *
     * @return string
     */
    public function createWidgetJwtToken($person)
    {
        return $this->container->get('widget_jwt_decoder')->encodePerson($person);
    }

    /**
     * @throws \Throwable
     *
     * @return string
     */
    public function getWidgetLoader()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $brand   = $this->getBrandStack()->getActive()->getBrand();
        /** @var WidgetBrandSettings $widgetOptions */
        $widgetOptions = $this->container->get('widget_settings_resolver')->getWidgetBrandOptions($brand);

        if (!$widgetOptions->getWidget()->isEnabled()) {
            return '';
        }

        return $this->container->get('widget_loader_code_renderer')->getWidgetCode($brand, $request, true);
    }

    /**
     * @param string $html     The content string
     * @param int    $len      Max chars to use
     * @param string $ellipses String to append when the string was truncated
     *
     * @return string
     */
    public function getHtmlContentPreview($html, $len, $ellipses = '…')
    {
        $html = Strings::decodeWhitespaceHtmlEntities($html);
        $html = Strings::decodeHtmlEntities($html);
        $html = Strings::stripTags($html);
        $html = preg_replace('#\s{2,}#', '', $html);

        if (isset($html[$len])) {
            $html = Strings::utf8_substr($html, 0, $len);
            $html = preg_replace('#\W$#u', '', $html); // strip non-word chars from end
            $html .= $ellipses;
        }

        return $html;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function shouldShowNavButtons()
    {
        $navigationHelper = $this->container->get('navigation_helper');

        return !$navigationHelper->hasOnlyOneApp() && !$navigationHelper->hasNoActiveApps();
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function safeLinkUrls($text)
    {
        return Strings::linkifyHtml(htmlspecialchars($text), true);
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function canLogin()
    {
        return $this->container->get('dp_authentication_manager.user')->isAuthVisible();
    }

    /**
     * @param CategoryAbstract|CommunityTopicStatusCategory $category
     */
    public function categoryColorCss($category)
    {
        if (!($category instanceof CategoryAbstract) && !($category instanceof CommunityTopicStatusCategory)) {
            throw new \InvalidArgumentException('the category_color_css twig function requires one of: CategoryAbstract or CommunityTopicStatusCategory but did not get one');
        }
        if ($category->getColor()) {
            return 'background-color: '.$category->getColor().';';
        }
    }

    /**
     * @param HasIconProperty $object
     * @param array           $options
     *
     * @throws Exception
     *
     * @return string
     */
    public function renderIconFrom(HasIconProperty $object, $options = [])
    {
        return $this->container->get('portal_icon.renderer')->getIconHtmlFrom($object, $options);
    }

    /**
     * @param IconProperty $icon
     * @param array        $options
     *
     * @throws Exception
     *
     * @return string
     */
    public function renderIcon(IconProperty $icon, $options = [])
    {
        return $this->container->get('portal_icon.renderer')->getIconHtml($icon, $options);
    }

    /**
     * @param HasSplashImageProperty $object
     *
     * @return bool
     */
    public function hasSplashImage(HasSplashImageProperty $object)
    {
        return (bool) $object->getSplashImage();
    }

    /**
     * @param HasSplashImageProperty $object
     * @param int                    $width
     * @param string                 $orientation
     *
     * @throws Exception
     *
     * @return string
     */
    public function getSplashUrl(HasSplashImageProperty $object, $width = 200, $orientation = 'landscape')
    {
        $splashImage = $object->getSplashImage();
        if ($splashImage) {
            return $this->container->get('splash_image.renderer')->getSplashUrl($splashImage, $width, $orientation);
        }

        return '';
    }

    /**
     * @param HasSplashImageProperty $object
     * @param string          $orientation
     *
     * @throws Exception
     *
     * @return string
     */
    public function getSplashBgcss(HasSplashImageProperty $object, $orientation = 'landscape')
    {
        $splashImage = $object->getSplashImage();
        if ($splashImage) {
            return $this->container->get('splash_image.renderer')->getSplashBgcss($splashImage, $orientation);
        }

        return '';
    }

    public function agentCanEdit(ContentAbstract $object = null)
    {
        if (!$this->getPerson()->isAgent()) {
            return false;
        }
        if (!$this->getPerson()->hasPerm('agent_publish.use')) {
            return false;
        }

        return $this->getPerson()->PermissionsManager->PublishChecker->canEdit($object);
    }

    public function highlightText($string, $word = '')
    {
        if (!$word) {
            return $string;
        }

        $word = preg_quote($word, '/');

        return preg_replace("/$word/i", "<b>\$0</b>", $string);
    }

    public function isCategorySubscribed($type, $category = null)
    {
        if (!in_array($type, ['news', 'kb', 'downloads'])) {
            return false;
        }
        if ($this->getPerson() && $this->getBrandSetting('user.'.$type.'_subscriptions', false)) {
            if ($category) {
                return $this->container->get('subscriptions_helper')->isSubscribedCategory($type, $this->getPerson());
            }

            return $this->container->get('subscriptions_helper')->isSubscribedRootCategory($type, $this->getPerson());
        }

        return false;
    }

    /**
     * @param string $path
     * @param string $packageName
     */
    public function getAssetDataUrl($path, $packageName)
    {
        /* @var $DP_ENV \DpRun\DpEnv */
        global $DP_ENV;

        $path = ltrim($path, '/');
        $type = ContentTypes::getContentTypeFromFilename($path);
        $data = null;

        switch ($packageName) {
            case 'legacy_web':
                $basePath = $DP_ENV->getAppWwwAssetDir().'/web';
                $file     = $basePath.'/'.$path;

                if (SafeFile::file_exists($file, $basePath)) {
                    $data = SafeFile::fileGetContents($file, $basePath);
                } else {
                    $data = '';
                }

                break;

            case 'help_center':
                $basePath = $DP_ENV->getAppWwwAssetDir().'/pub/build/DeskPRO/Bundle/PortalBundle/portal-style';
                $file     = $basePath.'/'.$path;
                $data     = SafeFile::fileGetContents($file, $basePath);
        }

        if ($data) {
            return 'data:'.$type.';base64,'.base64_encode($data);
        }

        return '';
    }

    /**
     * @throws \Exception
     *
     * @return PersonGuest|Entity\Person
     */
    public function getPerson()
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

    public function getCurrentTheme()
    {
        return $this->container->get('brand_stack')->getActive()->getBrand()->getThemeSet()->getThemeId();
    }

    public function getDeskproName()
    {
        return $this->getSettingsResolver()->getGlobalSettings()->get('core.deskpro_name');
    }

    /**
     * @throws \Exception
     *
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

    /**
     * @return string
     */
    public function getWidgetPhrasesJson()
    {
        $phrases = $this->container->get('dp.portal.languages.widget_phrase_translator')->translatePhrases();

        return json_encode($phrases);
    }
}
