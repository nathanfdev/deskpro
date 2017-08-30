<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
