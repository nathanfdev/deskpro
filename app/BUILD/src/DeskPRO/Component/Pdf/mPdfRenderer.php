<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\HttpFoundation\Response;

class mPdfRenderer implements PdfRendererInterface
{
    /**
     * @var \mPDF
     */
    private $object;

    /**
     * @var SettingsResolver
     */
    private $resolver;

    public function __construct($resolver, $tmpRootDir)
    {
        $this->resolver = $resolver;

        if (!defined('_MPDF_TTFONTDATAPATH')) {
            $tmpDir = $tmpRootDir.'/mpdf/ttfontdata';
            if (!is_dir($tmpDir)) {
                if (!@mkdir($tmpDir, 0777, true)) {
                    $tmpDir = sys_get_temp_dir().'/mpdf/ttfontdata';
                    if (!is_dir($tmpDir)) {
                        @mkdir($tmpDir, 0777, true);
                    }
                }
            }
            define('_MPDF_TTFONTDATAPATH', $tmpDir.'/');
        }

        $this->object = new \mPDF(
            'utf-8', // Language/Character set
            'A4', // Size
            '8', // Default Font Size
            '', // Default Font
            20, // Margin Left
            20, // Margin Right
            40, // Margin Top
            40, // Margin Bottom
            10, // Margin Header
            10, // Margin Footer
            'P' // Orientation
        );

        $this->object->SetBasePath($this->resolver->getGlobalSettings()->get('core.deskpro_url').'/');
        $this->object->shrink_tables_to_fit = 0;
    }

    public function setPageSize($size = 'A4', $orientation = 'P')
    {
        $this->object->_setPageSize($size, $orientation);
    }

    public function render($contentHtml)
    {
        $this->object->WriteHTML($contentHtml);

        return $this->object->Output('', 'S');
    }

    public function generateFile($contentHtml, $fileName)
    {
        $this->object->WriteHTML($contentHtml);

        return new Response($this->object->Output($fileName, 'D'), 200, ['Content-Type' => 'application/pdf']);
    }
}
