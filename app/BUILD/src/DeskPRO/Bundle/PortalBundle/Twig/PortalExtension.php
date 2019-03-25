<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Publish\GlossaryHandler;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
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
     * @return \DeskPRO\Bundle\BrandBundle\Brand\BrandStack
     */
    public function getBrandStack()
    {
        return $this->container->getBrandStack();
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
            new \Twig_SimpleFunction('has_login_form', [$this, 'hasLoginForm']),
            new \Twig_SimpleFunction('is_forgot_password_visible', [$this, 'isForgotPasswordVisible']),
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
            new \Twig_SimpleFunction('ticket_excerpts', [$this, 'getTicketExcerpts']),
            new \Twig_SimpleFunction('phrase_form_error', [$this, 'makeFormError'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('insert_glossary_js', [$this, 'makeGlossaryJs'], ['is_safe' => ['html', 'javascript']]),
            new \Twig_SimpleFunction('portal_mode', [$this, 'getPortalMode'], ['is_safe' => ['html', 'javascript']]),
            new \Twig_SimpleFunction('is_portal_widget_enabled', [$this, 'isPortalWidgetEnabled']),
            new \Twig_SimpleFunction('portal_widget_loader', [$this, 'getWidgetLoader'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('should_show_nav_buttons', [$this, 'shouldShowNavButtons']),
            new \Twig_SimpleFunction('can_login', [$this, 'canLogin']),

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
     * @param $phrase_name
     *
     * @return bool
     */
    public function hasPhrase($phrase_name)
    {
        return $this->container->get('deskpro.core.translate')->hasPhrase($phrase_name);
    }

    /**
     * @return string
     */
    public function getAuthUsersourcesJsObject()
    {
        return json_encode($this->container->get('usersources_view_helper')->createUsersourceViewList());
    }

    /**
     * @return bool
     */
    public function hasLoginForm()
    {
        return $this->container->get('usersources_view_helper')->hasLoginForm();
    }

    /**
     * @return bool
     */
    public function isForgotPasswordVisible()
    {
        return $auth_manager = $this->container->get('dp_authentication_manager.user')->isForgotPasswordVisible();
    }

    /**
     * @param $article
     *
     * @return string
     */
    public function makeGlossaryJs($article)
    {
        $brand          = $this->container->getBrandStack()->getActive()->getBrand();
        $glossary       = new GlossaryHandler($this->container->getEm(), $brand);
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
     * @param FormError $formError
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
     * @param array $tickets
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
        if (!$cat) {
            return [];
        }
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
     * @return string
     */
    public function getWidgetLoader()
    {
        $request       = $this->container->get('request_stack')->getCurrentRequest();
        $brand         = $this->getBrandStack()->getActive()->getBrand();
        $widgetOptions = $this->container->get('widget_settings_resolver')->getWidgetBrandOptions($brand);

        if (!$widgetOptions->getWidget()->isEnabled()) {
            return '';
        }

        return $this->container->get('widget_loader_code_renderer')->getWidgetCode($brand, $request, true, false);
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
     * @return bool
     */
    public function canLogin()
    {
        return $this->container->get('dp_authentication_manager.user')->isAuthVisible();
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
