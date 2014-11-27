<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\App;

use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Native\InstallerHandler\NoopInstallerHandler;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;

class InstanceInstaller
{
    /**
     * @var AppManager
     */
    private $manager;

    /**
     * @var \Application\DeskPRO\Entity\AppPackage
     */
    private $package;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param AppManager    $manager
     * @param AppPackage    $package
     * @param EntityManager $em
     */
    public function __construct(AppManager $manager, AppPackage $package, EntityManager $em)
    {
        $this->manager = $manager;
        $this->package = $package;
        $this->em      = $em;
    }


    /**
     * @param  string           $title
     * @param  array            $settings
     * @param  DeskproContainer $container
     * @return AppInstance
     */
    public function install($title, array $settings, DeskproContainer $container, $usersource_type = '')
    {
        $app = new AppInstance();
        $app->title = $title ?: $this->package->title;
        $app->package = $this->package;

        // Need to persist now so we have an actual app record
        // (the id may be used in the installer)
        $this->em->persist($app);
        $this->em->flush();

        $settings = self::readAppSettings($this->package, $settings);
        $context = $this->createInstallContext($this->package, $app, $settings, $container, $usersource_type);
        $handler = $this->createInstallHandler($this->package, $app);

        $settings = $handler->processSettings($context, $settings);
        $app->setSettings($settings ?: array());
        $this->em->persist($app);
        $this->em->flush();

        $handler->install($context);

        // if this package is a usersource package, we should always check with the manager to avoid invalid SSO configurations
        if ($context->getNativeApp() && $context->getPackage()->isUsersource()) {
            $container->getSystemService('usersource_manager')->ensureSsoSettings($context->getUsersource());
        }

        return $app;
    }


    /**
     * @param  AppPackage       $package
     * @param  AppInstance      $app
     * @param  array            $settings
     * @param  DeskproContainer $container
     * @param  null             $usersource_type
     * @return InstallerContext
     */
    protected function createInstallContext(AppPackage $package, AppInstance $app, array $settings, DeskproContainer $container, $usersource_type = null)
    {
        if ($package->native_name) {
            $native_app = $this->manager->getNativeApp($app);
            $usersource = null;

            if ($package->isUsersource()) {
                $usersource = new Usersource(); // this method only creates the installcontext for NEW app instances
                $usersource->app = $app;
                $usersource->title = $app->title;
                $usersource->type = $usersource_type;
            }

            return new InstallerContext($container, $native_app, $settings, $usersource);
        }

        // TODO this shouldnt be a native context, they need to be separate
        return new InstallerContext($container, null, $settings);
    }


    /**
     * Native apps have their own install handler (usually), but we always return the NoopInstallerHandler so we always have a handler
     *
     * @param  AppPackage                                                                 $package
     * @param  AppInstance                                                                $app
     * @return \Application\DeskPRO\App\Native\InstallerHandler\InstallerHandlerInterface
     */
    protected function createInstallHandler(AppPackage $package, AppInstance $app)
    {
        if ($package->native_name) {
            $native_app = $this->manager->getNativeApp($app);
            if ($class = $native_app->getConfig()->getInstallerHandlerClass()) {
                return new $class($this->package['settings_def']);
            }
        }

        return new NoopInstallerHandler();
    }


    /**
     * @param  AppPackage $package
     * @param  array      $settings_form
     * @return array
     */
    public static function readAppSettings(AppPackage $package, array $settings_form)
    {
        $settings = array();
        foreach ($package->settings_def as $setting_def) {
            $value = isset($settings_form[$setting_def['name']]) ? $settings_form[$setting_def['name']] : null;
            if (!is_scalar($value)) {
                $value = null;
            }

            if ($value !== null) {
                switch ($setting_def) {
                    case 'choice':
                        $found = false;
                        if (isset($setting_def['options'])) {
                            foreach ($setting_def['options'] as $opt) {
                                if ($opt['value'] == $value) {
                                    $found = true;
                                    break;
                                }
                            }
                        }
                        if (!$found) {
                            $value = null;
                        }
                        break;

                    case 'checkbox':
                        if ($value === true || $value === 1 || $value === "1" || $value === "true") {
                            $value = true;
                        } else {
                            $value = false;
                        }
                        break;
                }
            }

            if ($value === null && isset($setting_def['default_value'])) {
                $value = $setting_def['default_value'];
            }

            if ($value !== null) {
                $settings[$setting_def['name']] = $value;
            }

            #------------------------------------------
            # include dependant children
            # TODO: meant to be expanded to allow more options and diffent kinds of dependant fields
            #------------------------------------------
            if (isset($setting_def['inline_dependant'])) {
                $dep = $setting_def['inline_dependant'];
                $val = isset($settings_form[$dep['name']]) ? $settings_form[$dep['name']] : null;
                if ($val === null && isset($dep['default_value'])) {
                    $val = $dep['default_value'];
                }
                if ($val !== null) {
                    $settings[$dep['name']] = $val;
                }
            }
        }

        return $settings;
    }
}
