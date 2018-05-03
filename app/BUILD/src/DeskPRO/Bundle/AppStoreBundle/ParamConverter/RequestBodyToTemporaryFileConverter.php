<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DpRun\DpEnv;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestBodyToTemporaryFileConverter.
 */
class RequestBodyToTemporaryFileConverter implements ParamConverterInterface
{
    public static function createFromGlobals()
    {
        /** @var DpEnv $deskproEnv */
        $deskproEnv = $GLOBALS['DP_ENV'];

        return self::createFromDeskproEnv($deskproEnv);
    }

    public static function createFromDeskproEnv(DpEnv $env)
    {
        $tmpDir = $env->getUserTmpDir();
        if (empty($tmpDir)) {
            $tmpDir = sys_get_temp_dir();
        }

        return new static($tmpDir);
    }

    /** @var string */
    private $tmpDir;

    /**
     * RequestBodyToTemporaryFileConverter constructor.
     *
     * @param string $tmpDir
     */
    public function __construct($tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $file      = $this->writeRequestContentToFile($request);
        $converted = $this->applyConversion($file, $request, $configuration);

        if (!empty($converted)) {
            $attributeName = $configuration->getName();
            $request->attributes->set($attributeName, $converted);

            return true;
        }

        return false;
    }

    protected function applyConversion($file, Request $request, ParamConverter $configuration)
    {
        $fileInfo = new \SplFileInfo($file);

        return $fileInfo;
    }

    private function writeRequestContentToFile(Request $request)
    {
        $file = tempnam($this->tmpDir, 'deskpro_');
        file_put_contents($file, $request->getContent());

        return $file;
    }

    /**
     * @return string
     */
    private function writeInputStreamToFile()
    {
        $file = tempnam($this->tmpDir, 'deskpro_');
        file_put_contents($file, file_get_contents('php://input'));

        return $file;
    }

    public function supports(ParamConverter $configuration)
    {
        // TODO: Implement supports() method.
        // todo temp excluded tag request until proper statement is added
        return $configuration->getClass() !== TagRequest::class;
    }
}
