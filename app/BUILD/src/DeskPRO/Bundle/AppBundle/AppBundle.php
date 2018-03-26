<?php

namespace DeskPRO\Bundle\AppBundle;

use DeskPRO\Bundle\AppBundle\Command\Configure\ConfigElasticCommand;
use DeskPRO\Bundle\AppBundle\Command\Configure\ToggleFeatureCommand;
use DeskPRO\Bundle\AppBundle\Command\ServerInfo\WebServerInfoCommand;
use DeskPRO\Bundle\AppBundle\Command\Utility\DanglingBlobsCommand;
use DeskPRO\Bundle\AppBundle\Command\Utility\DanglingBlobStorageCommand;
use DeskPRO\Bundle\AppBundle\Command\Utility\ExportBlobCommand;
use DeskPRO\Bundle\AppBundle\Command\Utility\RecompileTemplatesCommand;
use DeskPRO\Bundle\AppBundle\Command\Utility\RefreshAgentInterfaceCommand;
use DeskPRO\Bundle\AppBundle\DependencyInjection\AppExtension;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\AppSecretPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\DbalConnectionPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\DoctrineCacheClassPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\FeaturesCompilerPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\FormOrderExtensionsPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\LazyWarmersPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\MongoConnectionPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\NotificationCompilerPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\PermissionGroupsCompilerPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\RegisterQuickSearchEventsPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\SerializerPass;
use DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler\TermEnginePass;
use DeskPRO\Bundle\AppBundle\Security\Factory\AgentImpersonateFactory;
use DeskPRO\Bundle\AppBundle\Security\Factory\DpFormLoginFactory;
use DeskPRO\Bundle\AppBundle\Security\Factory\TransferSessionAuthFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AppBundle.
 */
class AppBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new AppExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineCacheClassPass());
        $container->addCompilerPass(new LazyWarmersPass());
        $container->addCompilerPass(new AppSecretPass());
        $container->addCompilerPass(new FormOrderExtensionsPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new TermEnginePass());
        $container->addCompilerPass(new RegisterQuickSearchEventsPass());
        $container->addCompilerPass(new DbalConnectionPass());
        $container->addCompilerPass(new MongoConnectionPass());
        $container->addCompilerPass(new PermissionGroupsCompilerPass());
        $container->addCompilerPass(new FeaturesCompilerPass());
        $container->addCompilerPass(new NotificationCompilerPass());
        $container->addCompilerPass(new SerializerPass());

        $container->addCompilerPass(new Webhooks\DependencyInjection\CompilerPass());

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $security */
        $security = $container->getExtension('security');
        $security->addSecurityListenerFactory(new DpFormLoginFactory());
        $security->addSecurityListenerFactory(new AgentImpersonateFactory());
        $security->addSecurityListenerFactory(new TransferSessionAuthFactory());
    }

    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        $application->add(new WebServerInfoCommand());
        $application->add(new ConfigElasticCommand());
        $application->add(new ToggleFeatureCommand());
        $application->add(new RecompileTemplatesCommand());
        $application->add(new RefreshAgentInterfaceCommand());
        $application->add(new ExportBlobCommand());
        $application->add(new DanglingBlobsCommand());
        $application->add(new DanglingBlobStorageCommand());
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
