<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageParticipant;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\AppBundle\Security\AgentImpersonateToken;
use DeskPRO\Bundle\PortalBundle\SavedForm\SavedFormView;
use DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider;
use DeskPRO\Component\Util\LazyPropObject;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AbstractController extends BaseController
{
    /**
     * @return Person|null
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return Person|PersonGuest
     */
    public function getCurrentPerson()
    {
        return $this->getUser() ?: new PersonGuest();
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsBag
     */
    protected function getPermissionBagForCurrentUser()
    {
        return $this->getPermissionBag($this->getUser());
    }

    /**
     * @param Person $person
     *
     * @return \DeskPRO\Bundle\AppBundle\Security\Permissions\PermissionsBag
     */
    protected function getPermissionBag(Person $person = null)
    {
        if ($person) {
            return $this->get('portal_permissions_manager')->getPermissionsBagForPerson($person);
        }

        return $this->get('portal_permissions_manager')->getPermissionsBagForGuest();
    }

    /**
     * @return DeskproContainer|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\View\PageTitle\PageTitleGenerator
     */
    public function createPageTitle()
    {
        return $this->get('portal_view.page_title_generator');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\View\Breadcrumb\BreadcrumbGenerator
     */
    public function getBreadcrumbGenerator()
    {
        return $this->get('portal_view.breadcrumb_generator');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }

    public function persistAndFlushEntity($object)
    {
        $this->getEm()->persist($object);
        $this->getEm()->flush();
    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        return $this->getDoctrine()->getConnection();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter
     */
    public function getObjectRouter()
    {
        return $this->get('object_router');
    }

    /**
     * @param string $entity_name
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepo($entity_name)
    {
        return $this->getEm()->getRepository($entity_name);
    }

    /**
     * @param array $options
     *
     * @return \DeskPRO\Bundle\PortalBundle\Theme\ThemeView
     */
    public function createThemeView(array $options = [])
    {
        return $this->getThemeViewFactory()->createView($options);
    }

    public function renderThemeView($template_name, array $options = [])
    {
        $pageVars = [
            'page' => $this->createThemeView($options),
        ];

        // since "page" is a reserved template var, we need to rename it on the way in.
        // Templates use "pg" instead of "page" for that option name.
        $pg = false;
        if (array_key_exists('page', $options)) {
            $pg = $options['page'];
        }

        $pageData = new LazyPropObject([
            'flashes' => [$this, 'loadFlashes'],
            'alerts'  => [$this, 'loadAlerts'],
        ]);

        $pageVars['pageData']   = $pageData;
        $pageVars['helpcenter'] = $this->get('helpcenter_data_helper');

        // AGENT IMPERSONATION

        $agent           = null;
        $impersonationOn = false;

        $token = $this->get('security.token_storage')->getToken();
        if ($token) {
            if ($token instanceof AgentImpersonateToken) {
                $agentId         = $token->getAttribute(AgentImpersonateToken::ATTR_AGENT_IMPERSONATE);
                $agent           = $this->getPersonDataService()->getPerson($agentId);
                $impersonationOn = true;
            } else {
                $agent = $this->getCurrentPerson();
            }
        }

        // no bar for a non-agent
        if ($agent && $agent->isAgent()) {
            // no-agent-bar for focus window or preview
            $portalMode = $this->get('portal_mode_storage')->getMode();
            if ($portalMode && ($portalMode->isFocusWindow() || $portalMode->isAdminPreview())) {
                $agent           = null;
                $impersonationOn = false;
            }
        }

        $pageVars['impersonator']     = $agent;
        $pageVars['impersonation_on'] = $impersonationOn;
        $pageVars['active_brand_id']  = $this->getBrandContainer()->getBrand()->getId();

        $pageVars = array_merge($options, $pageVars);

        if ($pg) {
            $pageVars['pg'] = $pg;
        }

        return $this->render($template_name, $pageVars);
    }

    public function loadFlashes()
    {
        $request = $this->get('request');
        $flashes = [];
        $session = $request->getSession();
        if (null !== $session && $session->isStarted()) {
            $flashes = $session->getFlashBag()->all();
        }

        return $flashes;
    }

    public function loadAlerts()
    {
        $user = $this->getUser();

        $person   = $this->getCurrentPerson();
        $langDiff = false;
        if (!$person instanceof PersonGuest) {
            // user can click "dismiss" and we store a session var
            if (!$this->getSession()->get('ignore_language_warning', false)) {
                $activeLang = $this->get('language_stack')->getActiveOrDefault();
                $personLang = $person->getLanguage();

                if ($personLang) {
                    if ($personLang->getId() != $activeLang->getId()) {
                        $langDiff = [
                            'active_lang' => $activeLang,
                            'person_lang' => $personLang,
                        ];
                    }
                }
            }
        }

        // SAVED FORMS

        $savedForms = [];
        if ($user && $allSaved = $this->getFormSaver()->getSavedForms($user)) {
            foreach ($allSaved as $saved) {
                // We don't want people to validate their email address without going through their mailbox
                if ($saved->getIntentionType() == SavedForm::INTENTION_VERIFY_EMAIL) {
                    continue;
                }
                $savedForms[] = [
                    'message' => $this->getFormSaver()->getMessage($saved),
                    'link'    => $this->generateUrl('saved_form_auto_submit', ['auth_code' => $saved->getExternalCode()]),
                ];
            }
        }

        $brand = $this->getBrandContainer()->getBrand();

        // TICKETS AWAITING REPLY

        $ticketsAwaitingReply = [];
        if (!$person instanceof PersonGuest) {
            /** @var \Application\DeskPRO\EntityRepository\Ticket $ticketRepo */
            $ticketRepo           = $this->getRepo(Ticket::class);
            $ticketsAwaitingReply = $ticketRepo->getWaitingForReplyForPerson($person, 0, $brand);
        }

        $unreadDirectMessages = 0;
        if ($this->getBrandSetting('portal.members_community')) {
            $directMessageParticipantRepo = $this->getRepo(DirectMessageParticipant::class);
            $unreadDirectMessages         = $directMessageParticipantRepo->getUnreadThreadCount($person);
        }

        $approvalNeedingAction = 0;
        if ($this->getBrandSetting('core_tickets.ticket_approvals') && $user) {
            $approvalNeedingAction = $this->getTicketApprovalsDataService()->getApprovalCountWhereUserNeedAnAction($user, $brand);
        }

        $shouldDisplay = count($savedForms) || $langDiff || count($ticketsAwaitingReply);

        return [
            'user'                   => $user,
            'saved_forms'            => $savedForms,
            'display_alerts'         => $shouldDisplay,
            'lang_diff'              => $langDiff,
            'tickets_awaiting_reply' => $ticketsAwaitingReply,
            'unread_direct_messages' => $unreadDirectMessages,
            'pending_approvals'      => $approvalNeedingAction,
        ];
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Theme\ThemeViewFactory
     */
    public function getThemeViewFactory()
    {
        return $this->get('theme_view_factory');
    }

    /**
     * @param      $setting
     * @param null $default
     *
     * @return mixed
     */
    protected function getBrandSetting($setting, $default = null)
    {
        return $this->getBrandContainer()->getSetting($setting, $default);
    }

    /**
     * @return \DeskPRO\Bundle\BrandBundle\Brand\BrandContainer
     */
    public function getBrandContainer()
    {
        return $this->get('brand_stack')->getActive();
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandTheme
     */
    public function getPortalBrandTheme()
    {
        return $this->get('portal_brand_theme_loader')->getPortalBrandTheme($this->getBrandContainer()->getBrand());
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\SavedForm\FormSaver
     */
    protected function getFormSaver()
    {
        return $this->get('form_saver');
    }

    /**
     * Adds a flash message to the current session for type.
     *
     * @param string $type    The type
     * @param string $message The message
     *
     * @throws \LogicException
     */
    protected function addFlash($type, $message)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled.');
        }
        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param string $route      The name of the route
     * @param array  $parameters An array of parameters
     * @param int    $status     The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, array $parameters = [], $status = 302)
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes The attributes
     * @param mixed $object     The object
     *
     * @throws \LogicException
     *
     * @return bool
     */
    protected function isGranted($attributes, $object = null)
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
    }

    /**
     * Throws an exception unless the attributes are granted against the current authentication token and optionally
     * supplied object.
     *
     * @param mixed  $attributes The attributes
     * @param mixed  $object     The object
     * @param string $message    The message passed to the exception
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
    {
        if (!$this->isGranted($attributes, $object)) {
            throw $this->createAccessDeniedException($message);
        }
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Helper\PortalRatingsHelper
     */
    public function getRatingsHelper()
    {
        return $this->get('ratings_helper');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Helper\ContentSubscriptionsHelper
     */
    public function getSubscriptionsHelper()
    {
        return $this->get('subscriptions_helper');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\EmailSender\PortalEmailSender
     */
    protected function getEmailSender()
    {
        return $this->get('portal_email_sender');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\PersonDataService
     */
    public function getPersonDataService()
    {
        return $this->get('data.person');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse
     */
    public function getAntiAbuseService()
    {
        return $this->get('anti_abuse');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\EmailDataService
     */
    public function getEmailDataService()
    {
        return $this->get('data.email');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\RatingsDataService
     */
    public function getRatingDataService()
    {
        return $this->get('data.rating');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\DownloadsDataService
     */
    public function getDownloadsDataService()
    {
        return $this->get('data.downloads');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\NewsDataService
     */
    public function getNewsDataService()
    {
        return $this->get('data.news');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\Community\CommunityDataService
     */
    public function getCommunityDataService()
    {
        return $this->get('data.community');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\GuidesDataService
     */
    public function getGuidesDataService()
    {
        return $this->get('data.guides');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\ArticlesDataService
     */
    protected function getArticlesDataService()
    {
        return $this->get('data.articles');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\TicketsDataService
     */
    protected function getTicketsDataService()
    {
        return $this->get('data.tickets');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\Chat\ChatDataService
     */
    protected function getChatDataService()
    {
        return $this->get('data.chat');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\TicketApprovalsDataService
     */
    protected function getTicketApprovalsDataService()
    {
        return $this->get('data.ticket_approvals');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\DirectMessageThreadDataService
     */
    protected function getDirectMessageThreadDataService()
    {
        return $this->get('data.direct_message.thread');
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\DataService\TicketViewDataService
     */
    protected function getTicketsViewService()
    {
        return $this->get('tickets.view');
    }

    /**
     * @return \Application\DeskPRO\Tickets\TicketManager
     */
    protected function getTicketManager()
    {
        return $this->get('ticket_manager');
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Person\PersonFactory
     */
    protected function getPersonFactory()
    {
        return $this->get('person_factory');
    }

    /**
     * Leave language null unless you need a specific lang. The user's lang should already be in the
     * LanguageStack.
     *
     * If $phrase is an array, then any of these phrase IDs are expected to be suitable, and we will select
     * the best choice. E.g. if using helpcenter, then we will try to use a helpcenter phrase.
     *
     * @param string|string[] $phrase
     * @param array    $vars
     * @param Language $lang
     *
     * @return string
     */
    protected function phrase($phrase, array $vars = [], Language $lang = null)
    {
        if (is_array($phrase)) {
            if ($this->isHelpCenterTheme()) {
                $phrase = array_filter($phrase, function ($p) {
                    return strpos($p, 'helpcenter.') === 0;
                });
            } else {
                $phrase = array_filter($phrase, function ($p) {
                    return strpos($p, 'helpcenter.') !== 0;
                });
            }

            $phrase = array_pop($phrase);
        }

        return $this->get('language_manager')->phrase($phrase, $vars, $lang);
    }

    /**
     * @param               $object
     * @param null          $property
     * @param Language|null $lang
     *
     * @return string
     */
    protected function objectPhrase($object, $property = null, Language $lang = null)
    {
        return $this->get('language_manager')->objectPhrase($object, $property, $lang);
    }

    protected function makeJsonResponse(array $array)
    {
        $response = new JsonResponse(['data' => $array]);

        // if its 5.4+ make the results pretty
        if (constant('JSON_PRETTY_PRINT')) {
            $options = $response->getEncodingOptions();
            $options = $options | JSON_PRETTY_PRINT;
            $response->setEncodingOptions($options);
        }

        return $response;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConn()
    {
        return $this->getEm()->getConnection();
    }

    protected function submitSavedForm(SavedForm $saved_form, Request $request)
    {
        $savedFormView = new SavedFormView($saved_form);

        // prep the sub request to re-submit the form
        $data = $saved_form->getFormData();

        $url     = $this->generateUrl($savedFormView->getRouteName(), $savedFormView->getRouteParams());
        $baseUrl = $this->container->get('router')->getContext()->getBaseUrl();
        if ($baseUrl && strpos($url, $baseUrl) === 0) {
            $url = substr($url, strlen($baseUrl));
        }
        $subRequest = Request::create(
            $url,
            'POST',
            $data,
            $request->cookies->all()
        );

        // sub requests for saved forms have this attribute set
        $subRequest->attributes->set('saved-form', true);
        $subRequest->attributes->set(VisitorIdentificationProvider::ATTRIBUTE_NAME, $this->get('visitor_identification_provider')->getVisitorIdentifier());
        $subRequest->setSession($request->getSession());

        // get rid of the saved form now
        $this->getFormSaver()->markCompleted($saved_form);

        // submit the form again for the user
        $response = $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }

    protected function isSavedFormSubRequest(Request $request)
    {
        return $request->attributes->get('saved-form', false) ? true : false;
    }

    /**
     * @param ContentAbstract $content
     * @param                 $visitor_id
     *
     * @return \Application\DeskPRO\Entity\Rating|null
     */
    protected function findContentRating(ContentAbstract $content, $visitor_id)
    {
        if (!$rating = $this->getRatingsHelper()->getPersonRating($content, $this->getUser())) {
            $rating = $this->getRatingsHelper()->findVisitorRating($content, $visitor_id);
            if ($rating && $this->get('portal_cache_helper')->isGuestRequest()) {
                // this request is going to be cached for every non-session user, and so we cannot display
                // their rating on the page. If they rated recently, they'd have a session and would see it
                // because isGuestRequest() above would be false.
                // in the future maybe we could start a session here and redirect to this same page
                return;
            }
        }

        return $rating;
    }

    /**
     * @param ContentAbstract $content
     *
     * @return array
     */
    protected function determineRatingCounts(ContentAbstract $content)
    {
        $show_rating_counts = false;
        $rating_counts      = ['positive' => 0, 'total' => 0];
        if ($this->getBrandSetting('user.show_ratings')) {
            $rating_counts = $this->getRatingsHelper()->ratingCounts($content);
            if ($rating_counts['total'] >= $this->getBrandSetting('user.show_ratings_min_votes')) {
                $show_rating_counts = true;
            }
        }

        return [$show_rating_counts, $rating_counts];
    }

    /**
     * @return bool
     */
    public function isHelpCenterTheme()
    {
        return $this->getPortalBrandTheme()->getActiveThemeSet()->getThemeId() === 'helpcenter';
    }

    /**
     * @return bool
     */
    protected function isCommunityEnabled()
    {
        $settings = $this->container->get('settings_resolver');

        return $settings->getGlobalSettings()->get('portal.members_community');
    }
}
