<?php

namespace DeskPRO\Component\Pdf;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use Symfony\Component\HttpFoundation\Response;

interface PdfRendererInterface
{
    /**
     * PdfRendererInterface constructor.
     *
     * @param \DeskPRO\Bundle\BrandBundle\Brand\BrandStack $brandStack
     * @param AppEnv                                       $appEnv
     */
    public function __construct(BrandStack $brandStack, AppEnv $appEnv);

    /**
     * @param string $contentHtml
     *
     * @return string pdf content
     */
    public function render($contentHtml);

    /**
     * @param $contentHtml
     * @param $fileName
     *
     * @return Response
     */
    public function generateFile($contentHtml, $fileName);

    /**
     * @param string $size
     * @param string $orientation
     */
    public function setPageSize($size = 'A4', $orientation = 'P');
}
