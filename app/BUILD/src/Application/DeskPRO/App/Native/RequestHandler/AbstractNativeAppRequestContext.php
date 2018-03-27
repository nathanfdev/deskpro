<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\RequestHandler;

use Application\AgentBundle\Controller\AbstractController;
use Application\DeskPRO\App\Native\NativeApp;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractNativeAppRequestContext extends AbstractRequestContext
{
    /**
     * @var \Application\DeskPRO\App\Native\NativeApp
     */
    private $native_app;

    /**
     * @param DeskproContainer   $container
     * @param Request            $request
     * @param AbstractController $controller
     * @param NativeApp          $native_app
     * @param Person             $agent
     * @param $action
     */
    public function __construct(
        DeskproContainer $container,
        Request $request,
        AbstractController $controller,
        NativeApp $native_app,
        Person $agent,
        $action
    ) {
        $this->native_app = $native_app;
        parent::__construct(
            $container,
            $request,
            $controller,
            $agent,
            $native_app->getPackage(),
            $action
        );
    }

    /**
     * @return \Application\DeskPRO\Entity\AppInstance
     */
    public function getNativeApp()
    {
        return $this->native_app;
    }

    /**
     * @return \Application\DeskPRO\Entity\AppInstance
     */
    public function getApp()
    {
        return $this->native_app->getApp();
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getAppSetting($name, $default = null)
    {
        if (isset($GLOBALS['SETTINGS'])) {
            $setting_id          = $this->getPackage()->name;
            $setting_id_specific = $setting_id.'.'.$this->getApp()->id;

            if (isset($GLOBALS['SETTINGS'][$setting_id][$name])) {
                return $GLOBALS['SETTINGS'][$setting_id][$name];
            } elseif (isset($GLOBALS['SETTINGS'][$setting_id_specific][$name])) {
                return $GLOBALS['SETTINGS'][$setting_id_specific][$name];
            }
        }

        return $this->getApp()->getSetting($name, $default);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getAppService($name)
    {
        return $this->getContainer()->getAppManager()->getService($name, $this->getApp());
    }
}
