<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\RequestHandler;

use Application\DeskPRO\Controller\AbstractController;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\HttpFoundation\Request;

class AbstractRequestContext
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var \Application\DeskPRO\App\Native\NativePackageConfig
     */
    private $native_config;

    /**
     * @var \Application\DeskPRO\Entity\AppPackage
     */
    private $package;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    private $agent;

    /**
     * @var string
     */
    private $action;

    /**
     * @var \Application\AgentBundle\Controller\AbstractController
     */
    private $controller;

    /**
     * @param DeskproContainer   $container
     * @param Request            $request
     * @param AbstractController $controller
     * @param Person             $agent
     * @param AppPackage         $package
     * @param $action
     */
    public function __construct(
        DeskproContainer $container,
        Request $request,
        AbstractController $controller,
        Person $agent,
        AppPackage $package,
        $action
    ) {
        $this->container  = $container;
        $this->request    = $request;
        $this->controller = $controller;
        $this->agent      = $agent;
        $this->package    = $package;
        $this->action     = $action;

        $this->native_config = $container->getAppManager()->getNativePackageConfig($package);
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @return \Application\DeskPRO\App\Native\NativePackageConfig
     */
    public function getNativePackageConfig()
    {
        return $this->native_config;
    }

    /**
     * @return \Application\DeskPRO\Entity\AppPackage
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDb()
    {
        return $this->container->getDb();
    }

    /**
     * @return \Application\DeskPRO\Input\Reader
     */
    public function getIn()
    {
        return $this->container->getIn();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->container->getEm();
    }

    /**
     * @param string $message
     *
     * @return \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createNotFoundException($message = 'Not Found')
    {
        return $this->controller->createNotFoundException();
    }

    /**
     * Create a JSON response.
     *
     * @param string|array $content
     * @param int          $status_code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createJsonResponse($content, $status_code = 200)
    {
        return $this->controller->createJsonResponse($content, $status_code);
    }

    /**
     * Create a JSON response.
     *
     * @param string $content
     * @param int    $status_code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createResponse($content, $status_code = 200)
    {
        return $this->controller->createResponse($content, $status_code);
    }
}
