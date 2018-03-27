<?php

namespace DeskPRO\Component\Pdf;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Dompdf\Dompdf;

/**
 * Class domPdfRenderer.
 *
 * Implementation of DomPdf as a different renderer need work fixing the template and add domPdf to vendor
 * before usage.
 */
class domPdfRenderer implements PdfRendererInterface
{
    /**
     * @var Dompdf
     */
    private $object;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var AppEnv
     */
    private $appEnv;

    public function __construct(BrandStack $brandStack, AppEnv $appEnv)
    {
        $this->brandStack = $brandStack;

        $this->object = new Dompdf();

        $options = $this->object->getOptions();
//        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(true);

        $this->object->setOptions($options);
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $this->object->setProtocol($protocol);
        $full_path = $this->brandStack->getActive()->getSetting('core.deskpro_url');
        if (preg_match('|^https?://([^/]+)/?$|', $full_path, $matches)) {
            $host = $matches[1];
            $this->object->setBaseHost($host);
        }
    }

    public function setPageSize($size = 'A4', $orientation = 'P')
    {
        if ($orientation == '') {
            $domOrientation = 'portrait';
        } else {
            $domOrientation = 'landscape';
        }
        $this->object->setPaper($size, $domOrientation);
    }

    public function render($contentHtml)
    {
        $this->object->loadHtml($contentHtml);

        return $this->object->output();
    }

    public function generateFile($contentHtml, $fileName)
    {
        $this->object->loadHtml($contentHtml);

        $this->object->render();

        return $this->object->stream($fileName);
    }
}
