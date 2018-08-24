<?php

namespace DeskPRO\Bundle\ImportBundle\Source;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\ImporterTools\ImporterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SourceScriptResolver.
 */
class SourceScriptResolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $helpers;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param AppEnvInterface    $appEnv
     * @param LoggerInterface    $logger
     */
    public function __construct(ContainerInterface $container, AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->appEnv    = $appEnv;
        $this->logger    = $logger;
    }

    /**
     * @param mixed $helperId
     */
    public function addHelper($helperId)
    {
        $this->helpers[$helperId] = $helperId;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        $customPath  = $this->appEnv->getConfig('paths.importer_tools');
        $defaultPath = $this->appEnv->getAppDir().'/modules/importer-tools';

        if ($customPath && realpath($customPath)) {
            return realpath($customPath);
        }

        return realpath($defaultPath);
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function getConfigPath($filename)
    {
        return $this->getBasePath().'/importers/'.$filename.'/config.php';
    }

    /**
     * @param string $filename
     * @param array  $config
     *
     * @throws \Exception
     *
     * @return ImporterInterface
     */
    public function getSourceScript($filename, array $config)
    {
        // resolve importer script
        $scriptPath = $this->getBasePath().'/importers/'.$filename.'/src/'.ucfirst($filename).'/'.ucfirst($filename).'Importer.php';
        if (!file_exists($scriptPath)) {
            throw new \RuntimeException("File $scriptPath not found");
        }

        // register the script lib files in autoload
        $scriptBasePath = $this->getBasePath().'/importers/'.$filename;
        if (file_exists($scriptBasePath.'/autoload.php')) {
            require_once $scriptBasePath.'/autoload.php';
        } elseif (file_exists($scriptBasePath.'/vendor/autoload.php')) {
            require_once $scriptBasePath.'/vendor/autoload.php';
        }

        require_once $scriptPath;

        $sourceScriptClass = 'DeskPRO\\ImporterTools\\Importers\\'.ucfirst($filename).'\\'.ucfirst($filename).'Importer';
        if (!class_exists($sourceScriptClass)) {
            throw new \RuntimeException("Importer class $sourceScriptClass not found");
        }

        $reflection = new \ReflectionClass($sourceScriptClass);
        if (!$reflection->implementsInterface(ImporterInterface::class)) {
            throw new \RuntimeException('Importer class should implement '.ImporterInterface::class);
        }

        /** @var ImporterInterface $sourceScript */
        $sourceScript = new $sourceScriptClass($this->logger, $this->container);
        foreach ($this->helpers as $helperId) {
            $sourceScript->addHelper($this->container->get($helperId));
        }

        $sourceScript->init($config);

        return $sourceScript;
    }
}
