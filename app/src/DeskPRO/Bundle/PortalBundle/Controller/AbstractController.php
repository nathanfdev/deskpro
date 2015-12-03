<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $this->getEm()->flush($object);
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
    public function createThemeView(array $options = array())
    {
        return $this->getThemeViewFactory()->createView($options);
    }

    public function renderThemeView($template_name, array $options = array())
    {
        $page_vars = array(
            'page' => $this->createThemeView($options),
        );

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
     * @param $setting
     * @param null $default
     *
     * @return mixed
     */
    protected function getBrandSetting($setting, $default = null)
    {
        return $this->getBrandContainer()->getSetting($setting, $default);
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer
     */
    public function getBrandContainer()
    {
        return $this->get('brand_stack')->getActive();
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
    protected function redirectToRoute($route, array $parameters = array(), $status = 302)
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
     * @return \DeskPRO\Bundle\AppBundle\DataService\FeedbackDataService
     */
    public function getFeedbackDataService()
    {
        return $this->get('data.feedback');
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
     * @param $phrase
     * @param array    $vars
     * @param Language $lang
     *
     * @return string
     */
    protected function phrase($phrase, array $vars = array(), Language $lang = null)
    {
        return $this->get('language_manager')->phrase($phrase, $vars, $lang);
    }

    protected function makeJsonResponse(array $array)
    {
        $response = new JsonResponse(array('data' => $array));

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
}
