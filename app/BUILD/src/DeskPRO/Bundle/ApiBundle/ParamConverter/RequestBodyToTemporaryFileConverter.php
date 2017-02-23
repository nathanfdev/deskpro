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

namespace DeskPRO\Bundle\ApiBundle\ParamConverter;

use DpRun\DpEnv;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestBodyToTemporaryFileConverter
 * @package DeskPRO\Bundle\ApiBundle\ParamConverter
 */
class RequestBodyToTemporaryFileConverter implements ParamConverterInterface
{
    /** @var DpEnv */
    private $deskproEnv;

    public static function createFromGlobals()
    {
        return new RequestBodyToTemporaryFileConverter($GLOBALS['DP_ENV']);
    }

    /**
     * RequestBodyToTemporaryFileConverter constructor.
     * @param DpEnv $deskproEnv
     */
    public function __construct(DpEnv $deskproEnv)
    {
        $this->deskproEnv = $deskproEnv;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $attributeName = $configuration->getName();
        $file = $this->writeInputStreamToFile();
        $fileInfo = new \SplFileInfo($file);
        $request->attributes->set($attributeName, $fileInfo);
        return true;

    }

    /**
     * @return string
     */
    private function writeInputStreamToFile()
    {
        $tempDir = sys_get_temp_dir();
        $file = tempnam($tempDir, 'deskpro_');
        file_put_contents($file, file_get_contents('php://input'));

        return $file;
    }

    private function resolveTempDirectory(DpEnv $env)
    {
        $tmpDir = $env->getUserTmpDir();
        if (!empty($tmpDir)) {
            return $tmpDir;
        }

        return sys_get_temp_dir();
    }

    public function supports(ParamConverter $configuration)
    {
        // TODO: Implement supports() method.
        return true;
    }
}
