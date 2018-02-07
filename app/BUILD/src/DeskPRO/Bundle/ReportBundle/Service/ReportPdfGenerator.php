<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ReportBundle\Service;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class ReportPdfGenerator
 * Inspired by Browsershot (@link https://github.com/spatie/browsershot).
 */
class ReportPdfGenerator
{
    /**
     * @var string
     */
    private $pdfDir;

    /**
     * @var string
     */
    private $binaryPath;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var string
     */
    protected $includePath = '$PATH:/usr/local/bin';

    /**
     * ReportPdfGenerator constructor.
     *
     * @param AppEnvInterface  $appEnv
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(AppEnvInterface $appEnv, SettingsResolver $settingsResolver)
    {
        $build            = $appEnv->getBuildId() > 0 ? $appEnv->getBuildId() : 'BUILD';
        $this->pdfDir     = $appEnv->getUserTmpDir();
        $this->binaryPath =
            $appEnv->getWwwRoot().DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR
            .$build.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'bin';
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param $url
     * @param $name
     *
     * @return string
     */
    public function savePdf($url, $name)
    {
        $command = $this->createPdfCommand($url, $name);

        $setIncludePathCommand = "PATH={$this->includePath}";
        $nodeBinary            = 'node';
        $setNodePathCommand    = 'NODE_PATH=`npm root -g`';
        $binPath               = $this->binaryPath.DIRECTORY_SEPARATOR.'browser.js';
        $fullCommand           =
            $setIncludePathCommand.' '
            .$setNodePathCommand.' '
            .$nodeBinary.' '
            .escapeshellarg($binPath).' '
            .escapeshellarg(json_encode($command));

        $process = (new Process($fullCommand))->setTimeout(
            $this->settingsResolver->getGlobalSettings()->get('deskpro.pdf.generator.timeout', 60000)
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @param $url
     * @param $name
     *
     * @return array
     */
    private function createPdfCommand($url, $name)
    {
        $options = [
            'path'            => $this->pdfDir.DIRECTORY_SEPARATOR.$name,
            'printBackground' => true,
            'waitUntil'       => 'networkidle2',
            'landscape'       => true,
            'width'           => '297mm',
            'height'          => '210mm',
            'emulateMedia'    => 'screen',
            'viewport'        => ['height' => 1080, 'width' => 1920, 'isLandscape' => true, 'deviceScaleFactor' => 2],
        ];

        $command = [
            'url'     => $url,
            'action'  => 'pdf',
            'options' => $options,
        ];

        return $command;
    }
}
