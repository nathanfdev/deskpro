<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\InstallerHandler;

use Application\DeskPRO\App\Native\NativeApp;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Usersource;
use Orb\Util\Arrays;

class InstallerContext
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var \Application\DeskPRO\App\Native\NativeApp
     */
    private $native_app;

    /**
     * @var array
     */
    private $raw_form;

    /**
     * @var Usersource|null must be preset for "usersources" apps. null otherwise.
     */
    private $usersource;


    /**
     * @param DeskproContainer $container
     * @param NativeApp        $native_app
     * @param array            $raw_form
     * @param                  $usersource_type
     */
    public function __construct(DeskproContainer $container, NativeApp $native_app = null, array $raw_form = array(), Usersource $usersource = null)
    {
        $this->container       = $container;
        $this->native_app      = $native_app;
        $this->raw_form        = $raw_form;
        $this->usersource      = $usersource;
    }


    /**
     * @return array
     */
    public function getRawForm()
    {
        return $this->raw_form;
    }


    /**
     * @param  string $name
     * @return mixed
     */
    public function getRawFormData($name)
    {
        return Arrays::getValue($this->raw_form, $name);
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
     * @return \Application\DeskPRO\Entity\AppPackage
     */
    public function getPackage()
    {
        return $this->native_app->getPackage();
    }


    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return $this->container;
    }


    /**
     * @return Usersource|null
     */
    public function getUsersource()
    {
        return $this->usersource;
    }


    /**
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDb()
    {
        return $this->container->getDb();
    }


    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->container->getEm();
    }


    /**
     * @return \Application\DeskPRO\Usersource\UsersourceManager
     */
    public function getUsersourceManager()
    {
        return $this->container->getSystemService('usersource_manager');
    }
}
