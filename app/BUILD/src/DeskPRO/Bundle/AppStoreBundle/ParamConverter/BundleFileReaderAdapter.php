<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\BundleFileHandlingStrategyZip;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class BundleFileReaderAdapter implements ParamConverterInterface
{

    /** @var BundleFileHandlingStrategyZip  */
    private $bundleReaderStrategy;

    /** @var RequestBodyToTemporaryFileConverter  */
    private $requestBodyConverter;

    /**
     * BundleFileReaderAdapter constructor.
     * @param BundleFileHandlingStrategyZip $bundleReaderStrategy
     * @param RequestBodyToTemporaryFileConverter $requestBodyConverter
     */
    public function __construct(
        BundleFileHandlingStrategyZip $bundleReaderStrategy,
        RequestBodyToTemporaryFileConverter $requestBodyConverter
    ) {
        $this->bundleReaderStrategy = $bundleReaderStrategy;
        $this->requestBodyConverter = $requestBodyConverter;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply( Request $request, ParamConverter $configuration )
    {
        if ($this->requestBodyConverter->apply($request, $configuration)) {
            $attributeName = $configuration->getName();
            /** @var \SplFileInfo $fileInfo */
            $fileInfo = $request->attributes->get($attributeName);
            $bundleFile = $this->bundleReaderStrategy->readerForFileInfo($fileInfo);
            $request->attributes->set($attributeName, $bundleFile);
            return true;
        }

        return false;
    }

    public function supports(ParamConverter $configuration)
    {
        return $this->requestBodyConverter->supports($configuration);
    }
}
