<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppZipArchiveBundle;
use DpRun\DpEnv;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class AppZipArchiveBundleParamConverter extends RequestBodyToTemporaryFileConverter
{
    public static function createFromGlobals()
    {
        /** @var DpEnv $deskproEnv */
        $deskproEnv = $GLOBALS['DP_ENV'];
        return self::createFromDeskproEnv($deskproEnv);
    }

    /**
     * @param string $file
     * @param Request $request
     * @param ParamConverter $configuration
     * @return AppZipArchiveBundle
     */
    protected function applyConversion($file, Request $request, ParamConverter $configuration)
    {
        $fileInfo = new \SplFileInfo($file);
        $zipArchive = new \ZipArchive();

        return new AppZipArchiveBundle($zipArchive, $fileInfo);
    }
}
