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
 * @subpackage
 */

namespace Application\PortalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\DBAL\Connection;

class AbstractController extends BaseController
{
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->get('doctrine.orm.default_entity_manager');
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
     * @param string $entity_name
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepo($entity_name)
    {
        return $this->getEm()->getRepository($entity_name);
    }

    /**
     * @param array $options
     * @return \Application\PortalBundle\Theme\ThemeView
     */
    public function createThemeView(array $options = array())
    {
        return $this->getThemeViewFactory()->createView($options);
    }

    public function renderThemeView($template_name, array $options = array())
    {
        $page_vars = array(
            'page' => $this->createThemeView($options)
        );

        // since "page" is a reserved template var, we need to rename it on the way in.
        // Templates use "pg" instead of "page" for that option name.
        $pg = false;
        if (array_key_exists('page', $options)) {
            $pg = $options['page'];
        }

        // TODO: this is probably where we can create view objects??
        $page_vars = array_merge($options, $page_vars);

        if ($pg) {
            $page_vars['pg'] = $pg;
        }

        return $this->render($template_name, $page_vars);
    }

    /**
     * @return \Application\PortalBundle\Theme\ThemeViewFactory
     */
    public function getThemeViewFactory()
    {
        return $this->get('theme_view_factory');
    }

    /**
     * @return \Application\PortalBundle\Helper\PortalRatingsHelper
     */
    public function getRatingsHelper()
    {
        return $this->get('ratings_helper');
    }

    /**
     * @return \Application\PortalBundle\Helper\ContentSubscriptionsHelper
     */
    public function getSubscriptionsHelper()
    {
        return $this->get('subscriptions_helper');
    }

    /**
     * @return \Application\AppBundle\DataService\DownloadsDataService
     */
    public function getDownloadsDataService()
    {
        return $this->get('data.downloads');
    }

    /**
     * @return \Application\AppBundle\DataService\NewsDataService
     */
    public function getNewsDataService()
    {
        return $this->get('data.news');
    }

    /**
     * @return \Application\AppBundle\DataService\FeedbackDataService
     */
    public function getFeedbackDataService()
    {
        return $this->get('data.feedback');
    }

    /**
     * @param $setting
     * @param null $default
     * @return mixed
     */
    protected function getBrandSetting($setting, $default = null)
    {
        return $this->getBrandContainer()->getSetting($setting, $default);
    }

    /**
     * @return \Application\DeskPRO\Brand\BrandContainer
     */
    public function getBrandContainer()
    {
        return $this->get('brand_stack')->getActive();
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
     * @return \Application\AppBundle\DataService\ArticlesDataService
     */
    protected function getArticlesDataService()
    {
        return $this->get('data.articles');
    }
}
