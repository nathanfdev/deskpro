<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\InstallerHandler;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Usersource\ActionsCollection;
use Doctrine\ORM\EntityManager;

abstract class AbstractUsersourceInstallerHandler extends AbstractInstallerHandler
{
    protected $settingsDef;

    /**
     * @var InstallerContext
     */
    protected $context;

    public function __construct($settingsDef = [])
    {
        $this->settingsDef = $settingsDef;
    }

    /**
     * This is run during every install and update.
     *
     * @param AppInstance   $app
     * @param Usersource    $usersource
     * @param EntityManager $em
     *
     * @return mixed
     */
    abstract protected function applyAppToUsersource(AppInstance $app, Usersource $us, EntityManager $em);

    /**
     * Called after another app has enabled SSO. The underlying usersource is already cleared of its SSO status, but
     * you probably need to change your settings back to "off" mode (for exmaple, you need to manually "uncheck" the
     * "enable sso" checkbox here).
     *
     * @param AppInstance   $app
     * @param EntityManager $em
     */
    public function disableSsoSettings(AppInstance $app, EntityManager $em)
    {
        return;
    }

    public function setupActions()
    {
        if (!$context = $this->context) {
            throw new \RuntimeException('please ensure an installer context is present');
        }

        $us  = $context->getUsersource();
        $app = $context->getApp();

        if (Usersource::TYPE_AGENT === $us->type) {
            $us->auto_agent = $app->getSetting('auto_agent') ? true : false;

            if (!$us->auto_agent) {
                $us->actions = new ActionsCollection();

                return;
            }
        }

        $us->actions = ActionsCollection::unserializeJsonArray($app->getSetting('actions') ?: []);
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallerContext $context)
    {
        $this->context = $context;
        $this->applyAppToUsersource($context->getApp(), $context->getUsersource(), $context->getEm());
    }

    /**
     * {@inheritdoc}
     */
    public function updateSettings(InstallerContext $context)
    {
        $this->context = $context;
        $this->applyAppToUsersource($context->getApp(), $context->getUsersource(), $context->getEm());
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(InstallerContext $context)
    {
        $this->context = $context;
        $context->getEm()->remove($context->getUsersource());
        $context->getEm()->flush();
    }

    /**
     * @param InstallerContext $context
     * @param array            $settings
     *
     * @return array
     */
    public function processSettings(InstallerContext $context, array $settings)
    {
        $this->context = $context;

        return $settings;
    }

    /**
     * @param InstallerContext $context
     * @param array            $settings
     *
     * @return array
     */
    public function validateSettings(InstallerContext $context, array $settings)
    {
        $this->context = $context;

        return $settings;
    }

    /**
     * @param InstallerContext $context
     */
    public function updatePackage(InstallerContext $context)
    {
        $this->context = $context;
    }
}
