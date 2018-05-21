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

use Application\DeskPRO\Entity\SavedDashboardReport;
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
     * @param SavedDashboardReport $report
     *
     * @return array
     */
    public function calculatePrintConfig(SavedDashboardReport $report)
    {
        $maxX = 0;
        $maxY = 0;

        $blockToMmWidthRation  = 13.25; // got experimentaly
        $blockToMmHeightRation = 16.654; // got experimentaly
        $a4PaperRatio          = 1.4143; // 297/210

        $config = [
            'width'  => 0,
            'height' => 0,
        ];
        foreach ($report->getSavedWidgets() as $savedWidget) {
            $maxX = max($maxX, $savedWidget->getCol() + $savedWidget->getSizeX());
            $maxY = max($maxY, $savedWidget->getRow() + $savedWidget->getSizeY());
        }
        if ($maxX > $maxY) {
            $config['width']  = sprintf('%dmm', ceil($maxX * $blockToMmWidthRation) + 1);
            $config['height'] = sprintf('%dmm', ceil((int) $config['width'] / $a4PaperRatio) + 1);
        } else {
            $config['height'] = sprintf('%dmm', ceil($maxY * $blockToMmHeightRation) + 1);
            $config['width']  = sprintf('%dmm', ceil((int) $config['height'] / $a4PaperRatio) + 1);
        }

        return $config;
    }

    protected function getPdfName(SavedDashboardReport $report)
    {
        $name = sprintf('%s-%s.pdf', $report->getDateCreated()->format('Y-m-d_h:i:s'), $report->getTitle());

        return $this->pdfDir.DIRECTORY_SEPARATOR.$name;
    }

    /**
     * @param SavedDashboardReport $report
     * @param string               $url
     * @param string|null          $name
     *
     * @return string
     */
    public function savePdf(SavedDashboardReport $report, $url, $name = null)
    {
        if (!$name) {
            $name = $this->getPdfName($report);
        }

        $command = $this->createPdfCommand($url, $name);

        $printConfig        = $this->calculatePrintConfig($report);
        $command['options'] = array_merge($command['options'], $printConfig);

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

        return $name;
    }

    /**
     * @param string $url
     * @param string $name
     *
     * @return array
     */
    private function createPdfCommand($url, $name)
    {
        $options = [
            'path'            => $name,
            'printBackground' => true,
            'waitUntil'       => 'networkidle2',
            'landscape'       => false,
            'emulateMedia'    => 'screen',
        ];

        $command = [
            'url'     => $url,
            'action'  => 'pdf',
            'options' => $options,
        ];

        return $command;
    }
}
