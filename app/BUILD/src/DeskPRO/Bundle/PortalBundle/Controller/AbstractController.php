<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\ContentAbstract;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Entity\SavedForm;
use DeskPRO\Bundle\PortalBundle\SavedForm\SavedFormView;
use DeskPRO\Bundle\PortalBundle\Visitor\VisitorIdentificationProvider;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $page_vars = [
            'page' => $this->createThemeView($options),
        ];

        // since "page" is a reserved template var, we need to rename it on the way in.
        // Templates use "pg" instead of "page" for that option name.
        $pg = false;
        if (array_key_exists('page', $options)) {
            $pg = $options['page'];
        }

        $page_vars = array_merge($options, $page_vars);

        if ($pg) {
            $page_vars['pg'] = $pg;
        }

        return $this->render($template_name, $page_vars);
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
     * @return \DeskPRO\Bundle\AppBundle\DataService\Feedback\FeedbackDataService
     */
    public function getFeedbackDataService()
    {
        return $this->get('data.feedback');
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
     * @param          $phrase
     * @param array    $vars
     * @param Language $lang
     *
     * @return string
     */
    protected function phrase($phrase, array $vars = [], Language $lang = null)
    {
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
}
