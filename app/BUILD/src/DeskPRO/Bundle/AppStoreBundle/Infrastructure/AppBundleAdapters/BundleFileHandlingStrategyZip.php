<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters;

use Application\DeskPRO\DependencyInjection\SystemServices\ZipperService;
use DeskPRO\Bundle\AppStoreBundle\Domain;

class BundleFileHandlingStrategyZip
{
    /**
     * @var string
     */
    private $strategy;

    /**
     * @param array|string $availableExtensions list of available extensions
     * @return BundleFileHandlingStrategyZip
     */
    public static function instance(array $availableExtensions)
    {
        if (in_array(ZipperService::EXTENSION_ZIP, $availableExtensions)) {
            return new BundleFileHandlingStrategyZip(ZipperService::EXTENSION_ZIP);
        }

        if (in_array(ZipperService::EXTENSION_ZLIB, $availableExtensions)) {
            return new BundleFileHandlingStrategyZip(ZipperService::EXTENSION_ZLIB);
        }

        $msg = sprintf('unknown extensions: %s', implode(', ', $availableExtensions));
        throw new \DomainException($msg);
    }

    /**
     * AppBundleFileReader constructor.
     * @param string $strategy
     */
    private function __construct($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @param string $strategy
     * @return bool
     */
    public function usesStrategy($strategy)
    {
        return $this->strategy === $strategy;
    }

    /**
     * @param \SplFileInfo $file
     * @return PclzipAdapter|ZipArchiveAdapter
     */
    public function readerForFileInfo( \SplFileInfo $file) {
        if ($this->strategy === ZipperService::EXTENSION_ZIP) {
            return ZipArchiveAdapter::fromFile($file);
        }

        // fallback strategy to pclzip
        ZipperService::requirePclzip();
        return PclzipAdapter::fromFile($file);
    }

    /**
     * @param string $file
     * @return Domain\AppBundle
     */
    public function reader( $file) {
        return $this->readerForFileInfo(new \SplFileInfo($file));
    }

    public function writer( $file) {
        if ($this->strategy === ZipperService::EXTENSION_ZIP) {
            return ZipArchiveBundleWriter::fromFile($file);
        }

        // fallback strategy to pclzip
        ZipperService::requirePclzip();
        return PclzipBundleWriter::fromFile($file);
    }
}
