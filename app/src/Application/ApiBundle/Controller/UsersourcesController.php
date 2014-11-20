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

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Usersource;
use League\Url\Url;
use Orb\Auth\Adapter\CallbackInterface;
use Orb\Auth\Adapter\ExtraDetailsInterface;
use Orb\Auth\Adapter\IframeSsoInterface;
use Orb\Auth\Adapter\SsoLoginActionInterface;

class UsersourcesController extends AbstractController
{
    public function listByTypeAction($type)
    {
        if ($type == Usersource::TYPE_USER) {
            $sources = $this->getUsersourceManager()->getAll()->configuredForUsers(true);
        } else {
            $sources = $this->getUsersourceManager()->getAll()->configuredForAgents(true);
        }

        $usersources = array_map(
            function (Usersource $us) {
                return array('usersource' => $us->toApiData(), 'app' => $us->app ? $us->app->toApiData() : null);
            },
            (array) $sources
        );

        return $this->createApiResponse(array('usersources' => $usersources));
    }

    public function availableAppPackagesAction($interface)
    {
        $sources  = $this->getUsersourceManager()->getAll()->forInterface($interface, true);
        $packages = $this->container->getAppManager()->getAllPackages();

        // available packages are packages that are usersources, but have no usersource with an app instance
        // of that package.
        // TODO: support auth apps that are not single only
        $available_packages = array_filter($packages, function (AppPackage $package) use ($sources) {
                if ($package->isUsersource()) {
                    /** @var \Application\DeskPRO\Entity\Usersource $source */
                    foreach ($sources as $source) {
                        /** @var \Application\DeskPRO\Entity\AppInstance $app */
                        if ($app = $source->app) {
                            if ($app->package->name === $package->name) {
                                return false;
                            }
                        }
                    }

                    return true;
                }

                return false;
            }
        );

        $available_packages = array_map(
            function (AppPackage $package) {
                return $package->toApiData();
            },
            $available_packages
        );

        return $this->createApiResponse($available_packages);
    }

    public function getUsersourceAction($type, $id)
    {
        if ($id === 'deskpro') {
            $sources = $this->getUsersourceManager()->getAll()->withNoApp();
        } else {
            $sources = $this->getUsersourceManager()->getAll()->mustHaveId($id);
        }

        if ($type === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = $sources->getFirstOrNull();

        if (!$source) {
            throw $this->createNotFoundException('usersource id='.$id.' not found for type='.$type);
        }

        return $this->createApiResponse(
            array(
                'usersource' => $source->toApiData(),
                'app'        => $source->app ? $source->app->toApiData() : null,
            )
        );
    }

    public function getUsersourceExtraAction($type, $id)
    {
        $sources = $this->getUsersourceManager()->getAll();

        if ($type === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = null;
        foreach ($sources as $usersource) {
            if ($usersource->app && $usersource->app->id == $id) {
                $source = $usersource;
                break;
            }
        }

        if (!$source) {
            throw $this->createNotFoundException('usersource id='.$id.' not found');
        }

        $adapter = $this->container->getSystemService('usersource_auth_adapter_factory')->getAuthAdapter($source, null, $type);

        $details = array();
        if ($adapter instanceof ExtraDetailsInterface) {
            try {
                $details = $adapter->getExtraDetails();
            } catch (\Exception $e) {
            }
        }

        return $this->createApiResponse(
            array(
                'usersource_details' => $details,
            )
        );
    }

    public function postUsersourceAction($type, $id)
    {
        $source = $this->findUsersourceOfType($id, $type);

        if (!$source) {
            throw $this->createNotFoundException('usersource id='.$id.' not found for type='.$type);
        }

        $source->title      = $this->in->getString('title');
        $source->is_enabled = $this->in->getBool('is_enabled');
        $this->container->getEm()->persist($source);
        $this->container->getEm()->flush();

        return $this->createApiResponse(
            array(
                'usersource' => $source->toApiData(),
                'app'        => $source->app ? $source->app->toApiData() : null,
            )
        );
    }

    public function getIframeAction($app_id, $interface)
    {
        $sources = $this->getUsersourceManager()->getAll();

        if ($interface === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = null;
        foreach ($sources as $usersource) {
            if ($usersource->app && $usersource->app->id == $app_id) {
                $source = $usersource;
                break;
            }
        }

        if (!$source) {
            return $this->createApiErrorResponse(

                'not found',
                'could not find usersource for "'.$interface.'" interface with app id "'.$app_id.'"'
            );
        }

        /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $factory */
        $factory = $this->container->getSystemService('usersource_auth_adapter_factory');
        $adapter = $factory->getAuthAdapter($source, SsoLoginActionInterface::CONTEXT_BACKGROUND, $interface);

        if ($adapter instanceof CallbackInterface) {
            // append noredirect so that the callback url knows not to refresh the page on success
            $url                      = Url::createFromUrl($adapter->getCallbackUrl());
            $query                    = $url->getQuery();
            $query['usersource_test'] = true;
            $adapter->setCallbackUrl((string) $url);
        }

        if ($adapter instanceof IframeSsoInterface) {
            $vars = array_merge(
                array(
                    'iframe_url' => '',
                    'render'     => true,
                ),
                $adapter->getIframeTemplateParams(false)
            );

            return $this->createApiSuccessResponse(
                array(
                    'iframe_html' => $this->renderView(
                            'DeskPRO:Auth:_sso_iframe_for_test.html.twig',
                            $vars
                        ),
                )
            );
        }
    }

    public function updateDisplayOrderAction()
    {
        $inputOrders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
        $this->em->getRepository('DeskPRO:Usersource')->updateDisplayOrders($inputOrders);

        return $this->createApiSuccessResponse();
    }

    /**
     * @return \Application\DeskPRO\Usersource\UsersourceManager
     */
    protected function getUsersourceManager()
    {
        return $this->container->getSystemService('usersource_manager');
    }

    /**
     * @param $id
     * @param $type
     * @return Usersource|null
     */
    protected function findUsersourceOfType($id, $type)
    {
        if ($id === 'deskpro') {
            $sources = $this->getUsersourceManager()->getAll()->withNoApp();
        } else {
            $sources = $this->getUsersourceManager()->getAll()->mustHaveId($id);
        }

        if ($type === Usersource::TYPE_USER) {
            $sources = $sources->configuredForUsers(true);
        } else {
            $sources = $sources->configuredForAgents(true);
        }

        $source = $sources->getFirstOrNull();

        return $source;
    }
}
