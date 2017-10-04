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
